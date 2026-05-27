<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrazSubmission extends Model
{
    use SoftDeletes, LogsActivity;

    protected static function booted(): void
    {
        static::creating(function (PrazSubmission $submission) {
            if (empty($submission->reference_number)) {
                $year = date('Y');
                $last = static::withTrashed()
                    ->where('reference_number', 'like', "PRAZ-{$year}-%")
                    ->orderByDesc('reference_number')
                    ->value('reference_number');
                $next = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
                $submission->reference_number = 'PRAZ-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    protected $fillable = [
        'reference_number', 'title', 'tender_number', 'category',
        'client_id', 'procuring_entity', 'description',
        'submission_deadline', 'submitted_at', 'value', 'currency',
        'status', 'outcome_notes', 'prepared_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'submission_deadline' => 'datetime',
            'submitted_at'       => 'datetime',
            'value'              => 'decimal:2',
        ];
    }

    /**
     * Whether the submission deadline has passed without being submitted.
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::get(function () {
            return $this->submission_deadline
                && $this->submission_deadline->isPast()
                && !in_array($this->status, ['submitted', 'under_review', 'approved', 'rejected']);
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
