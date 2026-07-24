<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['invoiceNumber', 'supplierName', 'receivedBy', 'receivedAt', 'totalAmount', 'notes'])]
class IncomingStock extends Model
{
    protected function casts(): array
    {
        return [
            'receivedAt' => 'datetime',
            'totalAmount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receivedBy');
    }

    public function items(): HasMany
    {
        return $this->hasMany(IncomingStockItem::class, 'incomingStockId');
    }

    public function payable(): HasOne
    {
        return $this->hasOne(Payable::class, 'incomingStockId');
    }
}
