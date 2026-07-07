<?php

namespace App\Services;

use App\Enums\PartStatus;
use App\Models\OutgoingStock;
use App\Models\OutgoingStockItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Part;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OutgoingStockService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return OutgoingStock::query()
            ->with(['user', 'items.part', 'sale'])
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $data, User $user): OutgoingStock
    {
        return DB::transaction(function () use ($data, $user) {
            $outgoingStock = OutgoingStock::query()->create([
                'dispatchNumber' => $data['dispatchNumber'] ?? null,
                'recipientName' => $data['recipientName'] ?? null,
                'purpose' => strtoupper($data['purpose']), // 'SALE', 'TECHNICIAN', 'DAMAGED', 'RETURN', 'TRANSFER'
                'dispatchedBy' => $user->id,
                'dispatchedAt' => $data['dispatchedAt'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                OutgoingStockItem::query()->create([
                    'outgoingStockId' => $outgoingStock->id,
                    'partId' => $item['partId'],
                    'quantity' => $item['quantity'],
                ]);

                // Update Part Stock
                $part = Part::query()->findOrFail($item['partId']);
                $newQty = max(0, $part->quantity - $item['quantity']);
                
                $status = PartStatus::IN_STOCK;
                if ($newQty <= 0) {
                    $status = PartStatus::OUT_OF_STOCK;
                } elseif ($newQty <= 10) {
                    $status = PartStatus::LOW_STOCK;
                }

                $part->update([
                    'quantity' => $newQty,
                    'status' => $status,
                ]);
            }

            // If it is a SALE, automatically create a Sale record
            if (strtoupper($data['purpose']) === 'SALE') {
                $totalAmount = 0;
                $saleItemsData = [];

                foreach ($data['items'] as $item) {
                    $part = Part::query()->findOrFail($item['partId']);
                    // If unitPrice is passed in the request, use it. Otherwise, use part's price
                    $unitPrice = $item['unitPrice'] ?? $part->price;
                    $subtotal = $item['quantity'] * $unitPrice;
                    $totalAmount += $subtotal;

                    $saleItemsData[] = [
                        'partId' => $item['partId'],
                        'quantity' => $item['quantity'],
                        'unitPrice' => $unitPrice,
                        'subtotal' => $subtotal,
                    ];
                }

                $paymentStatus = strtoupper($data['paymentStatus'] ?? 'PAID');
                $amountPaid = $paymentStatus === 'PAID' ? $totalAmount : ($data['amountPaid'] ?? 0);

                $sale = Sale::query()->create([
                    'saleNumber' => $data['saleNumber'] ?? null,
                    'customerName' => $data['customerName'] ?? $data['recipientName'] ?? null,
                    'paymentStatus' => $paymentStatus,
                    'paymentMethod' => $data['paymentMethod'] ?? 'CASH',
                    'totalAmount' => $totalAmount,
                    'amountPaid' => $amountPaid,
                    'soldBy' => $user->id,
                    'soldAt' => $data['dispatchedAt'] ?? now(),
                    'outgoingStockId' => $outgoingStock->id,
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($saleItemsData as $saleItem) {
                    SaleItem::query()->create([
                        'saleId' => $sale->id,
                        'partId' => $saleItem['partId'],
                        'quantity' => $saleItem['quantity'],
                        'unitPrice' => $saleItem['unitPrice'],
                        'subtotal' => $saleItem['subtotal'],
                    ]);
                }
            }

            return $outgoingStock->fresh(['user', 'items.part', 'sale']);
        });
    }

    public function destroy(OutgoingStock $outgoingStock): void
    {
        DB::transaction(function () use ($outgoingStock) {
            foreach ($outgoingStock->items as $item) {
                $part = Part::query()->find($item->partId);
                if ($part) {
                    $newQty = $part->quantity + $item->quantity;
                    
                    $status = PartStatus::IN_STOCK;
                    if ($newQty <= 0) {
                        $status = PartStatus::OUT_OF_STOCK;
                    } elseif ($newQty <= 10) {
                        $status = PartStatus::LOW_STOCK;
                    }

                    $part->update([
                        'quantity' => $newQty,
                        'status' => $status,
                    ]);
                }
            }

            // Note: Cascade delete handles outgoing_stock_items.
            // If there's a linked sale, delete it (and cascade delete handles sale_items).
            if ($outgoingStock->sale) {
                $outgoingStock->sale->delete();
            }

            $outgoingStock->delete();
        });
    }
}
