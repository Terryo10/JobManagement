<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'category', 'status', 'priority',
        'assigned_to', 'created_by', 'start_date', 'due_date',
        'completed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'due_date'     => 'date',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AdminTask $task) {
            if (empty($task->created_by) && auth()->check()) {
                $task->created_by = auth()->id();
            }
        });

        static::updating(function (AdminTask $task) {
            if ($task->isDirty('status') && $task->status === 'completed' && ! $task->completed_at) {
                $task->completed_at = now();
            }
            if ($task->isDirty('status') && $task->status !== 'completed') {
                $task->completed_at = null;
            }
        });
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['completed', 'cancelled']);
    }

    public static function categories(): array
    {
        return [
            'general'    => 'General',
            'compliance' => 'Compliance',
            'hr'         => 'HR',
            'finance'    => 'Finance',
            'operations' => 'Operations',
            'strategic'  => 'Strategic',
        ];
    }

    public static function statuses(): array
    {
        return [
            'pending'     => 'Pending',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled',
        ];
    }

    public static function priorities(): array
    {
        return [
            'low'    => 'Low',
            'normal' => 'Normal',
            'high'   => 'High',
            'urgent' => 'Urgent',
        ];
    }
}
