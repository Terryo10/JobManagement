<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Quotation extends Model
{
    use LogsActivity;
    protected $fillable = [
        'quotation_number', 'client_id', 'phone', 'work_order_id', 'created_by',
        'status', 'currency', 'subtotal', 'tax_rate', 'tax_amount', 'total',
        'valid_until', 'notes', 'bank_account_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Quotation $quotation) {
            if (empty($quotation->quotation_number)) {
                $year = now()->year;
                $last = static::where('quotation_number', 'like', "QUO-{$year}-%")
                    ->orderByDesc('quotation_number')
                    ->value('quotation_number');
                $next = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
                $quotation->quotation_number = 'QUO-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'subtotal'    => 'decimal:2',
            'tax_rate'    => 'decimal:2',
            'tax_amount'  => 'decimal:2',
            'total'       => 'decimal:2',
            'valid_until' => 'date',
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

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
