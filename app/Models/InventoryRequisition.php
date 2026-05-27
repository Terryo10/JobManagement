<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class InventoryRequisition extends Model
{
    use LogsActivity;

    protected $fillable = [
        'reference_number', 'type', 'material_id', 'quantity_requested',
        'quantity_issued', 'requested_by', 'assigned_to', 'work_order_id',
        'status', 'approved_by', 'approved_at', 'procurement_requisition_id',
        'purchase_order_id', 'estimated_cost', 'notes', 'rejection_reason', 'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested'   => 'decimal:2',
            'quantity_issued'      => 'decimal:2',
            'estimated_cost'       => 'decimal:2',
            'approved_at'          => 'datetime',
            'issued_at'            => 'datetime',
        ];
    }

    // ─────────────────────────────────────────────
    // Auto-generate reference number on creation
    // ─────────────────────────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (InventoryRequisition $model) {
            if (empty($model->reference_number)) {
                $year = now()->year;
                $last = static::where('reference_number', 'like', "IREQ-{$year}-%")
                    ->orderByDesc('reference_number')
                    ->value('reference_number');
                $next = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
                $model->reference_number = 'IREQ-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * For an auto-issued inventory requisition: the procurement req that funded it.
     */
    public function procurementRequisition(): BelongsTo
    {
        return $this->belongsTo(InventoryRequisition::class, 'procurement_requisition_id');
    }

    /**
     * For a procurement requisition: the original inventory req it was raised to serve.
     * (Inverse of the self-referential FK above.)
     */
    public function originatingRequisition(): HasOne
    {
        return $this->hasOne(InventoryRequisition::class, 'procurement_requisition_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // ─────────────────────────────────────────────
    // Query Scopes
    // ─────────────────────────────────────────────

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeInStatus(Builder $query, string|array $status): Builder
    {
        return is_array($status)
            ? $query->whereIn('status', $status)
            : $query->where('status', $status);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('requested_by', $userId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    public function getActivityLogLabel(): string
    {
        return 'Inventory Requisition #' . $this->reference_number;
    }

    public function isInventoryType(): bool
    {
        return $this->type === 'inventory';
    }

    public function isProcurementType(): bool
    {
        return $this->type === 'procurement';
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Human-readable status label.
     */
    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'pending'         => 'Pending Review',
            'approved'        => 'Approved',
            'rejected'        => 'Rejected',
            'money_approved'  => 'Budget Approved',
            'money_issued'    => 'Funds Issued',
            'items_purchased' => 'Items Purchased',
            'items_received'  => 'Items Received',
            'issued'          => 'Issued',
            default           => Str::title(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Badge color for status.
     */
    public static function statusColor(string $status): string
    {
        return match ($status) {
            'pending'         => 'warning',
            'approved'        => 'info',
            'rejected'        => 'danger',
            'money_approved'  => 'info',
            'money_issued'    => 'primary',
            'items_purchased' => 'primary',
            'items_received'  => 'success',
            'issued'          => 'success',
            default           => 'gray',
        };
    }
}
