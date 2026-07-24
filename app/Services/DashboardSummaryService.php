<?php

namespace App\Services;

use App\Enums\PartStatus;
use App\Models\IncomingStock;
use App\Models\IncomingStockItem;
use App\Models\OutgoingStock;
use App\Models\OutgoingStockItem;
use App\Models\Part;
use App\Models\Payable;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;

class DashboardSummaryService
{
    public function getSummary(): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $salesThisMonth = Sale::query()
            ->whereBetween('soldAt', [$monthStart, $monthEnd]);
        $totalSales = (float) (clone $salesThisMonth)->sum('totalAmount');
        $amountPaid = (float) (clone $salesThisMonth)->sum('amountPaid');

        return [
            'inventory' => [
                'productCount' => Part::query()->count(),
                'quantity' => (int) Part::query()->sum('quantity'),
                'value' => (string) Part::query()
                    ->selectRaw('COALESCE(SUM(quantity * price), 0) as total')
                    ->value('total'),
                'lowStockCount' => Part::query()
                    ->where('status', PartStatus::LOW_STOCK->value)
                    ->count(),
                'outOfStockCount' => Part::query()
                    ->where('status', PartStatus::OUT_OF_STOCK->value)
                    ->count(),
            ],
            'monthlyTrends' => $this->monthlyTrends(),
            'salesOverview' => [
                'saleCount' => (clone $salesThisMonth)->count(),
                'totalSales' => (string) $totalSales,
                'amountPaid' => (string) $amountPaid,
                'collectionRate' => $totalSales > 0
                    ? round(($amountPaid / $totalSales) * 100, 1)
                    : 0,
            ],
            'recentActivities' => $this->recentActivities(),
            'alertCounts' => $this->alertCounts(),
            'topProducts' => $this->topProducts(),
        ];
    }

    private function monthlyTrends(): array
    {
        return collect(range(8, 0))
            ->map(function (int $monthsAgo): array {
                $month = now()->subMonths($monthsAgo);
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();

                return [
                    'month' => $month->format('Y-m'),
                    'stockIn' => (int) IncomingStockItem::query()
                        ->whereHas(
                            'incomingStock',
                            fn ($query) => $query->whereBetween('receivedAt', [$start, $end])
                        )
                        ->sum('quantity'),
                    'stockOut' => (int) OutgoingStockItem::query()
                        ->whereHas(
                            'outgoingStock',
                            fn ($query) => $query->whereBetween('dispatchedAt', [$start, $end])
                        )
                        ->sum('quantity'),
                    'salesRevenue' => (string) Sale::query()
                        ->whereBetween('soldAt', [$start, $end])
                        ->sum('totalAmount'),
                ];
            })
            ->all();
    }

    private function recentActivities(): array
    {
        $sales = Sale::query()
            ->with('items')
            ->latest('soldAt')
            ->limit(4)
            ->get()
            ->map(fn (Sale $sale): array => [
                'type' => 'SALE',
                'occurredAt' => $sale->soldAt?->toIso8601String(),
                'referenceNumber' => $sale->saleNumber,
                'partyName' => $sale->customerName,
                'amount' => (string) $sale->totalAmount,
                'quantity' => $sale->items->sum('quantity'),
            ]);

        $incoming = IncomingStock::query()
            ->with('items')
            ->latest('receivedAt')
            ->limit(4)
            ->get()
            ->map(fn (IncomingStock $stock): array => [
                'type' => 'INCOMING',
                'occurredAt' => $stock->receivedAt?->toIso8601String(),
                'referenceNumber' => $stock->invoiceNumber,
                'partyName' => $stock->supplierName,
                'amount' => (string) $stock->totalAmount,
                'quantity' => $stock->items->sum('quantity'),
            ]);

        $outgoing = OutgoingStock::query()
            ->with('items')
            ->whereDoesntHave('sale')
            ->latest('dispatchedAt')
            ->limit(4)
            ->get()
            ->map(fn (OutgoingStock $stock): array => [
                'type' => 'OUTGOING',
                'occurredAt' => $stock->dispatchedAt?->toIso8601String(),
                'referenceNumber' => $stock->dispatchNumber,
                'partyName' => $stock->recipientName,
                'amount' => null,
                'quantity' => $stock->items->sum('quantity'),
            ]);

        return $sales
            ->concat($incoming)
            ->concat($outgoing)
            ->sortByDesc('occurredAt')
            ->take(6)
            ->values()
            ->all();
    }

    private function alertCounts(): array
    {
        $today = now()->startOfDay()->toDateString();
        $threeDaysFromNow = now()->startOfDay()->addDays(3)->toDateString();

        return [
            'dueSoon' => Receivable::query()
                ->where('balanceAmount', '>', 0)
                ->whereDate('dueDate', '>=', $today)
                ->whereDate('dueDate', '<=', $threeDaysFromNow)
                ->count()
                + Payable::query()
                    ->where('balanceAmount', '>', 0)
                    ->whereDate('dueDate', '>=', $today)
                    ->whereDate('dueDate', '<=', $threeDaysFromNow)
                    ->count(),
            'lowStock' => Part::query()
                ->where('status', PartStatus::LOW_STOCK->value)
                ->count(),
            'outOfStock' => Part::query()
                ->where('status', PartStatus::OUT_OF_STOCK->value)
                ->count(),
        ];
    }

    private function topProducts(): array
    {
        $start = now()->subDays(29)->startOfDay();

        return SaleItem::query()
            ->select('partId')
            ->selectRaw('SUM(quantity) as quantity')
            ->selectRaw('SUM(subtotal) as revenue')
            ->whereHas('sale', fn ($query) => $query->where('soldAt', '>=', $start))
            ->with('part')
            ->groupBy('partId')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn (SaleItem $item): array => [
                'partName' => $item->part?->partName,
                'partNumber' => $item->part?->partNumber,
                'quantity' => (int) $item->quantity,
                'revenue' => (string) $item->revenue,
            ])
            ->all();
    }
}
