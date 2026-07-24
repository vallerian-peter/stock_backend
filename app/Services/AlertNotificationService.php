<?php

namespace App\Services;

use App\Enums\PartStatus;
use App\Enums\UserRole;
use App\Models\AlertNotification;
use App\Models\IncomingStock;
use App\Models\IncomingStockItem;
use App\Models\OutgoingStock;
use App\Models\OutgoingStockItem;
use App\Models\Part;
use App\Models\Payable;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AlertNotificationService
{
    public function syncFor(User $user): void
    {
        $definitions = [
            ...$this->stockDefinitions(),
            ...$this->dailyTrendDefinitions($user),
        ];

        if ($user->role === UserRole::ADMIN) {
            $definitions = [
                ...$definitions,
                ...$this->debtDefinitions(),
            ];
        }

        DB::transaction(function () use ($definitions, $user): void {
            $activeKeys = [];

            foreach ($definitions as $definition) {
                $activeKeys[] = $definition['sourceKey'];
                $notification = AlertNotification::query()
                    ->where('userId', $user->id)
                    ->where('sourceKey', $definition['sourceKey'])
                    ->first();

                if (! $notification) {
                    AlertNotification::query()->create([
                        ...$definition,
                        'userId' => $user->id,
                        'active' => true,
                    ]);

                    continue;
                }

                $conditionReactivated = ! $notification->active;
                $detailsChanged = $notification->details !== $definition['details'];

                $notification->fill([
                    ...$definition,
                    'active' => true,
                ]);

                if ($conditionReactivated || $detailsChanged) {
                    $notification->readAt = null;
                    $notification->dismissedAt = null;
                }

                $notification->save();
            }

            $inactiveQuery = AlertNotification::query()
                ->where('userId', $user->id)
                ->where('active', true);

            if ($activeKeys !== []) {
                $inactiveQuery->whereNotIn('sourceKey', $activeKeys);
            }

            $inactiveQuery->update(['active' => false]);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function stockDefinitions(): array
    {
        return Part::query()
            ->whereIn('status', [
                PartStatus::LOW_STOCK->value,
                PartStatus::OUT_OF_STOCK->value,
            ])
            ->orderBy('quantity')
            ->get()
            ->map(function (Part $part): array {
                $isOutOfStock = $part->status === PartStatus::OUT_OF_STOCK;
                $type = $isOutOfStock ? 'OUT_OF_STOCK' : 'LOW_STOCK';

                return [
                    'sourceKey' => sprintf('part:%d:%s', $part->id, $type),
                    'type' => $type,
                    'severity' => $isOutOfStock ? 'critical' : 'warning',
                    'redirectTo' => '/dashboard/products',
                    'details' => [
                        'partName' => $part->partName,
                        'partNumber' => $part->partNumber,
                        'quantity' => $part->quantity,
                        'price' => (string) $part->price,
                    ],
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function debtDefinitions(): array
    {
        $today = now()->startOfDay();
        $lastDueDate = $today->copy()->addDays(3);
        $definitions = [];

        Receivable::query()
            ->where('balanceAmount', '>', 0)
            ->whereNotNull('dueDate')
            ->whereDate('dueDate', '>=', $today->toDateString())
            ->whereDate('dueDate', '<=', $lastDueDate->toDateString())
            ->get()
            ->each(function (Receivable $record) use (&$definitions, $today): void {
                $definitions[] = [
                    'sourceKey' => sprintf(
                        'receivable:%d:%s',
                        $record->id,
                        $record->dueDate->toDateString()
                    ),
                    'type' => 'DEBT_DUE_RECEIVABLE',
                    'severity' => 'warning',
                    'redirectTo' => '/dashboard/debts/receivable',
                    'details' => [
                        'partyName' => $record->customerName,
                        'referenceNumber' => $record->referenceNumber,
                        'balanceAmount' => (string) $record->balanceAmount,
                        'totalAmount' => (string) $record->totalAmount,
                        'dueDate' => $record->dueDate->toDateString(),
                        'daysRemaining' => (int) $today->diffInDays($record->dueDate, false),
                    ],
                ];
            });

        Payable::query()
            ->where('balanceAmount', '>', 0)
            ->whereNotNull('dueDate')
            ->whereDate('dueDate', '>=', $today->toDateString())
            ->whereDate('dueDate', '<=', $lastDueDate->toDateString())
            ->get()
            ->each(function (Payable $record) use (&$definitions, $today): void {
                $definitions[] = [
                    'sourceKey' => sprintf(
                        'payable:%d:%s',
                        $record->id,
                        $record->dueDate->toDateString()
                    ),
                    'type' => 'DEBT_DUE_PAYABLE',
                    'severity' => 'warning',
                    'redirectTo' => '/dashboard/debts/payable',
                    'details' => [
                        'partyName' => $record->creditorName,
                        'referenceNumber' => $record->referenceNumber,
                        'balanceAmount' => (string) $record->balanceAmount,
                        'totalAmount' => (string) $record->totalAmount,
                        'dueDate' => $record->dueDate->toDateString(),
                        'daysRemaining' => (int) $today->diffInDays($record->dueDate, false),
                    ],
                ];
            });

        return $definitions;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function dailyTrendDefinitions(User $user): array
    {
        $reportDate = now()->subDay()->startOfDay();
        $reportEnd = $reportDate->copy()->endOfDay();

        $sales = Sale::query()
            ->whereBetween('soldAt', [$reportDate, $reportEnd]);
        $incomingStocks = IncomingStock::query()
            ->whereBetween('receivedAt', [$reportDate, $reportEnd]);
        $outgoingStocks = OutgoingStock::query()
            ->whereBetween('dispatchedAt', [$reportDate, $reportEnd]);
        $saleOutgoingIds = (clone $outgoingStocks)
            ->whereHas('sale')
            ->pluck('id');

        $details = [
            'reportDate' => $reportDate->toDateString(),
            'salesCount' => (clone $sales)->count(),
            'salesRevenue' => (string) (clone $sales)->sum('totalAmount'),
            'salesPaid' => (string) (clone $sales)->sum('amountPaid'),
            'soldQuantity' => SaleItem::query()
                ->whereHas('sale', fn ($query) => $query->whereBetween('soldAt', [$reportDate, $reportEnd]))
                ->sum('quantity'),
            'incomingRecords' => (clone $incomingStocks)->count(),
            'incomingQuantity' => IncomingStockItem::query()
                ->whereHas('incomingStock', fn ($query) => $query->whereBetween('receivedAt', [$reportDate, $reportEnd]))
                ->sum('quantity'),
            'incomingCost' => (string) (clone $incomingStocks)->sum('totalAmount'),
            'outgoingRecords' => (clone $outgoingStocks)->count(),
            'outgoingQuantity' => OutgoingStockItem::query()
                ->whereHas('outgoingStock', fn ($query) => $query->whereBetween('dispatchedAt', [$reportDate, $reportEnd]))
                ->sum('quantity'),
            'saleDispatchQuantity' => $saleOutgoingIds->isEmpty()
                ? 0
                : OutgoingStockItem::query()->whereIn('outgoingStockId', $saleOutgoingIds)->sum('quantity'),
            'otherDispatchQuantity' => OutgoingStockItem::query()
                ->whereHas('outgoingStock', function ($query) use ($reportDate, $reportEnd): void {
                    $query
                        ->whereBetween('dispatchedAt', [$reportDate, $reportEnd])
                        ->whereDoesntHave('sale');
                })
                ->sum('quantity'),
        ];

        return [[
            'sourceKey' => 'daily-trend:'.$reportDate->toDateString(),
            'type' => 'DAILY_TREND',
            'severity' => 'info',
            'redirectTo' => $user->role === UserRole::ADMIN
                ? '/dashboard/analytics'
                : '/dashboard/sales',
            'details' => $details,
        ]];
    }
}
