<?php

namespace App\Http\Resources\Receivable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'saleId' => $this->saleId,
            'customerName' => $this->customerName,
            'customerPhone' => $this->customerPhone,
            'referenceNumber' => $this->referenceNumber,
            'totalAmount' => (string) $this->totalAmount,
            'amountPaid' => (string) $this->amountPaid,
            'balanceAmount' => (string) $this->balanceAmount,
            'status' => $this->status,
            'debtDate' => $this->debtDate?->toDateString(),
            'dueDate' => $this->dueDate?->toDateString(),
            'notes' => $this->notes,
            'createdBy' => $this->createdBy,
            'createdByName' => $this->creator ? trim(($this->creator->fullName ?? '') ?: ($this->creator->firstName . ' ' . $this->creator->lastName)) : null,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
