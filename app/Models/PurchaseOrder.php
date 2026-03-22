<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'title', 'reference_number', 'status', 'ordered_by',
        'approved_by', 'finance_approved_by', 'total_amount',
        'expected_delivery', 'delivered_at', 'notes',
        'work_order_id', 'attachment',
        'finance_signature', 'finance_signature_date',
        'admin_signature', 'admin_signature_date',
    ];

    protected function casts(): array
    {
        return [
            'expected_delivery'     => 'date',
            'delivered_at'          => 'datetime',
            'total_amount'          => 'decimal:2',
            'finance_signature_date' => 'datetime',
            'admin_signature_date'  => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function financeApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function financialApprovals(): MorphMany
    {
        return $this->morphMany(FinancialApproval::class, 'approvable');
    }
}
