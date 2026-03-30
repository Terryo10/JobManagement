<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeletionRequest extends Model
{
    protected $fillable = [
        'requested_by',
        'reviewed_by',
        'subject_type',
        'subject_id',
        'subject_label',
        'reason',
        'status',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    /* ── Relationships ───────────────────────────────────────── */

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /* ── Scopes ──────────────────────────────────────────────── */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /* ── Actions ─────────────────────────────────────────────── */

    public function approve(int $reviewerId): bool
    {
        $subject = $this->subject;

        if (! $subject) {
            // Record already gone
            $this->update([
                'status'      => 'approved',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);
            return false;
        }

        // Delete the record (soft-delete if the model supports it)
        $subject->delete();

        // Log the approval
        ActivityLog::create([
            'user_id'       => $reviewerId,
            'action'        => 'deletion_approved',
            'subject_type'  => $this->subject_type,
            'subject_id'    => $this->subject_id,
            'subject_label' => $this->subject_label,
            'properties'    => ['requested_by' => $this->requested_by],
        ]);

        $this->update([
            'status'      => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);

        return true;
    }

    public function reject(int $reviewerId): void
    {
        ActivityLog::create([
            'user_id'       => $reviewerId,
            'action'        => 'deletion_rejected',
            'subject_type'  => $this->subject_type,
            'subject_id'    => $this->subject_id,
            'subject_label' => $this->subject_label,
            'properties'    => ['requested_by' => $this->requested_by],
        ]);

        $this->update([
            'status'      => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }
}
