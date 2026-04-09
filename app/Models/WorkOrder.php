<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class WorkOrder extends Model
{
    use SoftDeletes, LogsActivity;

    protected static function booted(): void
    {
        static::creating(function (WorkOrder $workOrder) {
            if (empty($workOrder->created_by) && auth()->check()) {
                $workOrder->created_by = auth()->id();
            }

            if (empty($workOrder->reference_number)) {
                $year = date('Y');
                $last = static::withTrashed()
                    ->where('reference_number', 'like', "WO-{$year}-%")
                    ->orderByDesc('reference_number')
                    ->value('reference_number');
                $next = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
                $workOrder->reference_number = 'WO-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    protected $fillable = [
        'reference_number', 'client_id', 'lead_id', 'title', 'description',
        'category', 'status', 'priority', 'budget', 'actual_cost',
        'budget_alert_threshold', 'assigned_department_id', 'start_date',
        'deadline', 'completed_at', 'created_by', 'details',
        'claimed_by', 'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'deadline' => 'date',
            'completed_at' => 'datetime',
            'claimed_at' => 'datetime',
            'details' => 'array',
            'budget' => 'decimal:2',
            'actual_cost' => 'decimal:2',
        ];
    }

    /**
     * Claim this work order for the given user (with pessimistic locking).
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
                'status' => 'in_progress',
            ]);
            return true;
        });
    }

    /**
     * Release this work order so it returns to the queue.
     */
    public function release(): void
    {
        $this->update([
            'claimed_by' => null,
            'claimed_at' => null,
        ]);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'assigned_department_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(WorkOrderNote::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function taskTimeLogs(): HasManyThrough
    {
        return $this->hasManyThrough(TaskTimeLog::class, Task::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(WorkOrderMaterial::class);
    }

    public function safetyRecords(): HasMany
    {
        return $this->hasMany(SafetyComplianceRecord::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'work_order_collaborators')
            ->withPivot('role', 'added_at');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
