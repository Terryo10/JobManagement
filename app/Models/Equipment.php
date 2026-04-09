<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Equipment extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name', 'serial_number', 'category', 'division', 'status',
        'current_work_order_id', 'purchase_date', 'next_maintenance_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'next_maintenance_date' => 'date',
        ];
    }

    public function currentWorkOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'current_work_order_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
