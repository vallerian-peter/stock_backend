<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'referenceNumber',
    'userId',
    'type',
    'category',
    'subject',
    'message',
    'priority',
    'contactPreference',
    'rating',
    'status',
    'locale',
    'sourcePath',
    'sheetSyncStatus',
    'sheetSyncedAt',
    'sheetSyncError',
])]
class SupportRequest extends Model
{
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'sheetSyncedAt' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
