<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SafetyComplianceRecord extends Model
{
    use LogsActivity;
    protected $fillable = [
        'work_order_id', 'checklist_item', 'is_complete',
        'completed_by', 'completed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_complete' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
