<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['saleNumber', 'customerName', 'paymentStatus', 'paymentMethod', 'totalAmount', 'amountPaid', 'soldBy', 'soldAt', 'outgoingStockId', 'notes'])]
class Sale extends Model
{
    protected function casts(): array
    {
        return [
            'soldAt' => 'datetime',
            'totalAmount' => 'decimal:2',
            'amountPaid' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'soldBy');
    }

    public function outgoingStock(): BelongsTo
    {
        return $this->belongsTo(OutgoingStock::class, 'outgoingStockId');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'saleId');
    }

    public function receivable(): HasOne
    {
        return $this->hasOne(Receivable::class, 'saleId');
    }
}
