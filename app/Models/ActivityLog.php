<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties'  => 'array',
            'created_at'  => 'datetime',
        ];
    }

    /* ── Relationships ───────────────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /* ── Scopes ──────────────────────────────────────────────── */

    public function scopeOlderThan($query, int $days)
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    public function getShortSubjectType(): string
    {
        return class_basename($this->subject_type);
    }
}
