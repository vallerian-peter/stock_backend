<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'lowStockThreshold',
    'allowNegativeStock',
])]
class WorkspaceSetting extends Model
{
    protected function casts(): array
    {
        return [
            'lowStockThreshold' => 'integer',
            'allowNegativeStock' => 'boolean',
        ];
    }
}
