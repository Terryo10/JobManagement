<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderMaterial extends Model
{
    protected $fillable = [
        'work_order_id', 'material_id', 'quantity_used',
        'unit_cost_at_time', 'logged_by', 'logged_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
            'quantity_used' => 'decimal:2',
            'unit_cost_at_time' => 'decimal:2',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
