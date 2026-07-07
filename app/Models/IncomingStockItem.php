<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['incomingStockId', 'partId', 'quantity', 'unitCost', 'subtotal'])]
class IncomingStockItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unitCost' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function incomingStock(): BelongsTo
    {
        return $this->belongsTo(IncomingStock::class, 'incomingStockId');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'partId');
    }
}
