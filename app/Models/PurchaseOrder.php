<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use LogsActivity;
    protected $fillable = [
        'title', 'reference_number', 'status', 'ordered_by',
        'approved_by', 'finance_approved_by', 'total_amount', 'currency',
        'expected_delivery', 'delivered_at', 'notes',
        'work_order_id', 'attachments',
        'finance_signature', 'finance_signature_date',
        'admin_signature', 'admin_signature_date',
        'gl_account', 'gl_account_name',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (PurchaseOrder $purchaseOrder) {
            if (empty($purchaseOrder->reference_number)) {
                $year = now()->year;
                $next = DB::transaction(function () use ($year): int {
                    if (! DB::table('requisition_sequences')->where('year', $year)->exists()) {
                        // Seed the counter from deployed requisitions the first time a year is used.
                        $lastNumber = 0;
                        foreach (DB::table('purchase_orders')
                            ->where('reference_number', 'like', "REQ-{$year}-%")
                            ->cursor() as $order) {
                            if (preg_match("/^REQ-{$year}-(\d+)$/", $order->reference_number, $matches)) {
                                $lastNumber = max($lastNumber, (int) $matches[1]);
                            }
                        }

                        DB::table('requisition_sequences')->insertOrIgnore([
                            'year' => $year,
                            'last_number' => $lastNumber,
                        ]);
                    }

                    $sequence = DB::table('requisition_sequences')
                        ->where('year', $year)
                        ->lockForUpdate()
                        ->first();

                    $next = $sequence->last_number + 1;
                    DB::table('requisition_sequences')
                        ->where('year', $year)
                        ->update(['last_number' => $next]);

                    return $next;
                });
                $purchaseOrder->reference_number = 'REQ-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'expected_delivery'     => 'date',
            'delivered_at'          => 'datetime',
            'total_amount'          => 'decimal:2',
            'finance_signature_date' => 'datetime',
            'admin_signature_date'  => 'datetime',
            'attachments'           => 'array',
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
