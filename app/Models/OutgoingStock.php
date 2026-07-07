<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['dispatchNumber', 'recipientName', 'purpose', 'dispatchedBy', 'dispatchedAt', 'notes'])]
class OutgoingStock extends Model
{
    protected function casts(): array
    {
        return [
            'dispatchedAt' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatchedBy');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OutgoingStockItem::class, 'outgoingStockId');
    }

    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class, 'outgoingStockId');
    }
}
