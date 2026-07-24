<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['saleId', 'customerName', 'customerPhone', 'referenceNumber', 'totalAmount', 'amountPaid', 'balanceAmount', 'status', 'debtDate', 'dueDate', 'notes', 'createdBy'])]
class Receivable extends Model
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

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'saleId');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createdBy');
    }
}
