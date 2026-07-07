<?php

namespace App\Http\Resources\IncomingStock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomingStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoiceNumber' => $this->invoiceNumber,
            'supplierName' => $this->supplierName,
            'receivedBy' => $this->receivedBy,
            'receivedByName' => $this->user ? trim(($this->user->fullName ?? '') ?: ($this->user->firstName . ' ' . $this->user->lastName)) : null,
            'receivedAt' => $this->receivedAt?->toIso8601String(),
            'totalAmount' => (string) $this->totalAmount,
            'notes' => $this->notes,
            'createdAt' => $this->created_at?->toIso8601String(),
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'partId' => $item->partId,
                    'partName' => $item->part?->partName,
                    'partNumber' => $item->part?->partNumber,
                    'quantity' => $item->quantity,
                    'unitCost' => (string) $item->unitCost,
                    'subtotal' => (string) $item->subtotal,
                ];
            }),
        ];
    }
}
