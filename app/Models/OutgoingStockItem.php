<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['outgoingStockId', 'partId', 'quantity'])]
class OutgoingStockItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function outgoingStock(): BelongsTo
    {
        return $this->belongsTo(OutgoingStock::class, 'outgoingStockId');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'partId');
    }
}
