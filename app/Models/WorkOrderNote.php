<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrderNote extends Model
{
    protected $fillable = ['work_order_id', 'user_id', 'body', 'is_internal', 'document_ids'];

    protected function casts(): array
    {
        return [
            'is_internal'  => 'boolean',
            'document_ids' => 'array',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Documents referenced in this comment.
     */
    public function referencedDocuments(): HasMany
    {
        return $this->workOrder
            ->documents()
            ->whereIn('id', $this->document_ids ?? []);
    }
}
