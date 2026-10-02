<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'order_number', 'quotation_id', 'work_order_id', 'client_id', 'status',
        'currency', 'subtotal', 'tax_rate', 'tax_amount', 'total', 'order_date',
        'required_date', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'order_date' => 'date',
            'required_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SalesOrder $order) {
            if (blank($order->order_number)) {
                $year = now()->year;
                $last = static::withTrashed()
                    ->where('order_number', 'like', "SO-{$year}-%")
                    ->orderByDesc('order_number')
                    ->value('order_number');
                $next = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
                $order->order_number = "SO-{$year}-".str_pad($next, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }
}
