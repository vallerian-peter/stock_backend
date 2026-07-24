<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SaleService
{
    protected OutgoingStockService $outgoingStockService;

    public function __construct(OutgoingStockService $outgoingStockService)
    {
        $this->outgoingStockService = $outgoingStockService;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Sale::query()
            ->with(['user', 'items.part', 'outgoingStock', 'receivable'])
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $data, User $user): Sale
    {
        return DB::transaction(function () use ($data, $user) {
            // Map Sale request to OutgoingStock payload
            $outgoingPayload = [
                'dispatchNumber' => $data['saleNumber'] ?? null,
                'purpose' => 'SALE',
                'dispatchedAt' => $data['soldAt'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'items' => array_map(function ($item) {
                    return [
                        'partId' => $item['partId'],
                        'quantity' => $item['quantity'],
                        'unitPrice' => $item['unitPrice'] ?? null,
                    ];
                }, $data['items']),
                'customerName' => $data['customerName'] ?? null,
                'customerPhone' => $data['customerPhone'] ?? null,
                'isDebt' => $data['isDebt'] ?? false,
                'debtDueDate' => $data['debtDueDate'] ?? null,
                'paymentStatus' => $data['paymentStatus'] ?? 'PAID',
                'paymentMethod' => $data['paymentMethod'] ?? 'CASH',
                'amountPaid' => $data['amountPaid'] ?? null,
                'additionalAmount' => $data['additionalAmount'] ?? 0,
                'saleNumber' => $data['saleNumber'] ?? null,
            ];

            $outgoingStock = $this->outgoingStockService->store($outgoingPayload, $user);

            return $outgoingStock->sale->fresh(['user', 'items.part', 'receivable']);
        });
    }

    public function destroy(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            if ($sale->outgoingStock) {
                // OutgoingStockService destroy will delete the linked sale and restore stock
                $this->outgoingStockService->destroy($sale->outgoingStock);
            } else {
                $sale->delete();
            }
        });
    }
}
