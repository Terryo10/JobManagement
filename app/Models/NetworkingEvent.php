<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkingEvent extends Model
{
    protected $fillable = [
        'name', 'type', 'location', 'start_date', 'end_date',
        'description', 'attendees', 'outcomes', 'leads_generated', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'attendees' => 'array',
            'leads_generated' => 'integer',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
