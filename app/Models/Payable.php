<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['incomingStockId', 'creditorName', 'creditorPhone', 'referenceNumber', 'totalAmount', 'amountPaid', 'balanceAmount', 'status', 'debtDate', 'dueDate', 'notes', 'createdBy'])]
class Payable extends Model
{
    protected function casts(): array
    {
        return [
            'totalAmount' => 'decimal:2',
            'amountPaid' => 'decimal:2',
            'balanceAmount' => 'decimal:2',
            'debtDate' => 'date',
            'dueDate' => 'date',
        ];
    }

    public function incomingStock(): BelongsTo
    {
        return $this->belongsTo(IncomingStock::class, 'incomingStockId');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createdBy');
    }
}
