<?php

namespace App\Http\Resources\Sale;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'saleNumber' => $this->saleNumber,
            'customerName' => $this->customerName,
            'paymentStatus' => $this->paymentStatus,
            'paymentMethod' => $this->paymentMethod,
            'totalAmount' => (string) $this->totalAmount,
            'amountPaid' => (string) $this->amountPaid,
            'soldBy' => $this->soldBy,
            'soldByName' => $this->user ? trim(($this->user->fullName ?? '') ?: ($this->user->firstName . ' ' . $this->user->lastName)) : null,
            'soldAt' => $this->soldAt?->toIso8601String(),
            'notes' => $this->notes,
            'createdAt' => $this->created_at?->toIso8601String(),
            'outgoingStockId' => $this->outgoingStockId,
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'partId' => $item->partId,
                    'partName' => $item->part?->partName,
                    'partNumber' => $item->part?->partNumber,
                    'quantity' => $item->quantity,
                    'unitPrice' => (string) $item->unitPrice,
                    'subtotal' => (string) $item->subtotal,
                ];
            }),
        ];
    }
}
