<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'invoice_number', 'quotation_id', 'work_order_id', 'client_id', 'status',
        'subtotal', 'tax_rate', 'tax_amount', 'total', 'currency',
        'issued_at', 'due_at', 'paid_at', 'payment_method',
        'payment_reference', 'notes', 'created_by',
        'client_signature', 'client_signature_date', 'client_ip',
        'bank_account_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (filled($invoice->invoice_number)) {
                return;
            }

            $year = now()->year;
            $next = DB::transaction(function () use ($year): int {
                if (! DB::table('invoice_sequences')->where('year', $year)->exists()) {
                    $lastNumber = 0;
                    foreach (DB::table('invoices')
                        ->where('invoice_number', 'like', "INV-{$year}-%")
                        ->cursor() as $existing) {
                        if (preg_match("/^INV-{$year}-(\\d+)$/", $existing->invoice_number, $matches)) {
                            $lastNumber = max($lastNumber, (int) $matches[1]);
                        }
                    }

                    DB::table('invoice_sequences')->insertOrIgnore([
                        'year' => $year,
                        'last_number' => $lastNumber,
                    ]);
                }

                $sequence = DB::table('invoice_sequences')->where('year', $year)->lockForUpdate()->first();
                $next = $sequence->last_number + 1;
                DB::table('invoice_sequences')->where('year', $year)->update(['last_number' => $next]);

                return $next;
            });

            $invoice->invoice_number = "INV-{$year}-".str_pad($next, 4, '0', STR_PAD_LEFT);
        });
    }

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'due_at' => 'date',
            'paid_at' => 'datetime',
            'client_signature_date' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
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
        return $this->hasMany(InvoiceItem::class);
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
