<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Expense extends Model
{
    protected $fillable = [
        'work_order_id', 'category', 'amount', 'currency',
        'description', 'expense_date', 'submitted_by',
        'approved_by', 'approval_status', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount'       => 'decimal:2',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function financialApprovals(): MorphMany
    {
        return $this->morphMany(FinancialApproval::class, 'approvable');
    }
}
