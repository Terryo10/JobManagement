<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadCommunication extends Model
{
    protected $fillable = [
        'lead_id', 'user_id', 'type', 'summary',
        'outcome', 'next_action', 'next_action_date',
    ];

    protected function casts(): array
    {
        return ['next_action_date' => 'date'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
