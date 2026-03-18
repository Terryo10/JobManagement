<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use SoftDeletes;

    public ?string $unassignmentReason = null;

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (empty($task->created_by) && auth()->check()) {
                $task->created_by = auth()->id();
            }
        });
    }

    protected $fillable = [
        'work_order_id', 'parent_task_id', 'depends_on_task_id',
        'title', 'description', 'assigned_to', 'department_id',
        'status', 'priority', 'estimated_hours', 'actual_hours',
        'completion_percentage', 'start_date', 'deadline', 'completed_at', 'created_by',
        'claimed_by', 'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'deadline' => 'date',
            'completed_at' => 'datetime',
            'claimed_at' => 'datetime',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
        ];
    }

    /**
     * Claim this task for the given user (with pessimistic locking).
     */
    public function claim(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $locked = static::lockForUpdate()->find($this->id);
            if ($locked->claimed_by) {
                return false;
            }
            $locked->update([
                'claimed_by' => $user->id,
                'claimed_at' => now(),
                'assigned_to' => $user->id,
                'status' => 'in_progress',
            ]);
            return true;
        });
    }

    /**
     * Release this task so it returns to the queue.
     */
    public function release(): void
    {
        $this->update([
            'claimed_by' => null,
            'claimed_at' => null,
            'assigned_to' => null,
            'status' => 'pending',
        ]);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'depends_on_task_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TaskTimeLog::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
