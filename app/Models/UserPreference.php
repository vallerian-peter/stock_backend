<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'userId',
    'theme',
    'locale',
    'compactTables',
    'lowStockAlerts',
    'salesDigest',
    'debtReminders',
])]
class UserPreference extends Model
{
    protected function casts(): array
    {
        return [
            'compactTables' => 'boolean',
            'lowStockAlerts' => 'boolean',
            'salesDigest' => 'boolean',
            'debtReminders' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
