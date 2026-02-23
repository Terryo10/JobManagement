<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAvailability extends Model
{
    protected $fillable = [
        'user_id', 'unavailable_from', 'unavailable_to',
        'reason', 'notes', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'unavailable_from' => 'date',
            'unavailable_to' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
