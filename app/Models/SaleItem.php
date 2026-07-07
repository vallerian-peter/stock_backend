<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['saleId', 'partId', 'quantity', 'unitPrice', 'subtotal'])]
class SaleItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unitPrice' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'saleId');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'partId');
    }
}
