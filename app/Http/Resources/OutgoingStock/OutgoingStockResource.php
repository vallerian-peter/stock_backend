<?php

namespace App\Http\Resources\OutgoingStock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutgoingStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dispatchNumber' => $this->dispatchNumber,
            'recipientName' => $this->recipientName,
            'purpose' => $this->purpose,
            'dispatchedBy' => $this->dispatchedBy,
            'dispatchedByName' => $this->user ? trim(($this->user->fullName ?? '') ?: ($this->user->firstName . ' ' . $this->user->lastName)) : null,
            'dispatchedAt' => $this->dispatchedAt?->toIso8601String(),
            'notes' => $this->notes,
            'createdAt' => $this->created_at?->toIso8601String(),
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'partId' => $item->partId,
                    'partName' => $item->part?->partName,
                    'partNumber' => $item->part?->partNumber,
                    'quantity' => $item->quantity,
                ];
            }),
            'sale' => $this->sale ? [
                'id' => $this->sale->id,
                'saleNumber' => $this->sale->saleNumber,
                'customerName' => $this->sale->customerName,
                'paymentStatus' => $this->sale->paymentStatus,
                'paymentMethod' => $this->sale->paymentMethod,
                'totalAmount' => (string) $this->sale->totalAmount,
                'amountPaid' => (string) $this->sale->amountPaid,
                'receivableId' => $this->sale->receivable?->id,
            ] : null,
        ];
    }
}
