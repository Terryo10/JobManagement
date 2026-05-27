<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DesignBrief extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'title',
        'client_id',
        'work_order_id',
        'created_by',
        'designer_id',
        'status',
        'priority',
        'objective',
        'target_audience',
        'deliverables',
        'dimensions_specifications',
        'copy_text',
        'branding_guidelines',
        'notes_references',
        'deadline',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
