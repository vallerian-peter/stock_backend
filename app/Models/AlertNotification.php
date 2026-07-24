<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'userId',
    'sourceKey',
    'type',
    'severity',
    'redirectTo',
    'details',
    'readAt',
    'dismissedAt',
    'active',
])]
class AlertNotification extends Model
{
    protected function casts(): array
    {
        return [
            'details' => 'array',
            'readAt' => 'datetime',
            'dismissedAt' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
