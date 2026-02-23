<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeadlineEscalation extends Model
{
    protected $fillable = [
        'escalatable_type', 'escalatable_id', 'escalation_level',
        'escalated_to', 'reason', 'overdue_hours_at_escalation', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function escalatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function escalatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }
}
