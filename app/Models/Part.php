<?php

namespace App\Models;

use App\Enums\PartStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['partName', 'partNumber', 'quantity', 'price', 'imageUrl', 'imageLastModifiedAt', 'categoryId', 'status'])]
class Part extends Model
{
    protected function casts(): array
    {
        return [
            'imageLastModifiedAt' => 'integer',
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'status' => PartStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categoryId');
    }
}
