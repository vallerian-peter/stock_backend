<?php

namespace App\Services;

use App\Enums\PartStatus;
use App\Models\IncomingStock;
use App\Models\IncomingStockItem;
use App\Models\Part;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IncomingStockService
{
    public function __construct(private readonly PayableService $payableService)
    {
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return IncomingStock::query()
            ->with(['user', 'items.part', 'payable'])
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $data, User $user): IncomingStock
    {
        return DB::transaction(function () use ($data, $user) {
            $incomingStock = IncomingStock::query()->create([
                'invoiceNumber' => $data['invoiceNumber'] ?? null,
                'supplierName' => $data['supplierName'] ?? null,
                'receivedBy' => $user->id,
                'receivedAt' => $data['receivedAt'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'totalAmount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unitCost'];
                $totalAmount += $subtotal;

                IncomingStockItem::query()->create([
                    'incomingStockId' => $incomingStock->id,
                    'partId' => $item['partId'],
                    'quantity' => $item['quantity'],
                    'unitCost' => $item['unitCost'],
                    'subtotal' => $subtotal,
                ]);

                // Update Part Stock
                $part = Part::query()->findOrFail($item['partId']);
                $newQty = $part->quantity + $item['quantity'];
                
                $status = PartStatus::IN_STOCK;
                if ($newQty <= 0) {
                    $status = PartStatus::OUT_OF_STOCK;
                } elseif ($newQty <= 15) {
                    $status = PartStatus::LOW_STOCK;
                }

                $part->update([
                    'quantity' => $newQty,
                    'status' => $status,
                ]);
            }

            $incomingStock->update(['totalAmount' => $totalAmount]);

            if ($data['isDebt'] ?? false) {
                if ($totalAmount <= 0 || (float) ($data['amountPaid'] ?? 0) >= $totalAmount) {
                    throw ValidationException::withMessages([
                        'amountPaid' => ['A debt intake must have an unpaid balance.'],
                    ]);
                }

                $this->payableService->store([
                    'incomingStockId' => $incomingStock->id,
                    'creditorName' => $data['supplierName'],
                    'creditorPhone' => $data['supplierPhone'] ?? null,
                    'referenceNumber' => $data['invoiceNumber'] ?? null,
                    'totalAmount' => $totalAmount,
                    'amountPaid' => $data['amountPaid'] ?? 0,
                    'debtDate' => $incomingStock->receivedAt->toDateString(),
                    'dueDate' => $data['debtDueDate'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ], $user);
            }

            return $incomingStock->fresh(['user', 'items.part', 'payable']);
        });
    }

    public function destroy(IncomingStock $incomingStock): void
    {
        DB::transaction(function () use ($incomingStock) {
            foreach ($incomingStock->items as $item) {
                $part = Part::query()->find($item->partId);
                if ($part) {
                    $newQty = max(0, $part->quantity - $item->quantity);
                    
                    $status = PartStatus::IN_STOCK;
                    if ($newQty <= 0) {
                        $status = PartStatus::OUT_OF_STOCK;
                    } elseif ($newQty <= 15) {
                        $status = PartStatus::LOW_STOCK;
                    }

                    $part->update([
                        'quantity' => $newQty,
                        'status' => $status,
                    ]);
                }
            }

            $incomingStock->delete();
        });
    }
}
