<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryTransaction extends Model
{
    /**
     * This is an append-only ledger. Only created_at is tracked.
     */
    public $timestamps = false;

    protected $fillable = [
        'material_id', 'transaction_type', 'quantity',
        'balance_before', 'balance_after',
        'reference_type', 'reference_id',
        'performed_by', 'notes', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity'       => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after'  => 'decimal:2',
            'created_at'     => 'datetime',
        ];
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * The record that caused this transaction (e.g. an InventoryRequisition).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo('reference');
    }

    // ─────────────────────────────────────────────
    // Query Scopes
    // ─────────────────────────────────────────────

    public function scopeAdditions(Builder $query): Builder
    {
        return $query->where('transaction_type', 'addition');
    }

    public function scopeDeductions(Builder $query): Builder
    {
        return $query->where('transaction_type', 'deduction');
    }

    public function scopeForMaterial(Builder $query, int $materialId): Builder
    {
        return $query->where('material_id', $materialId);
    }

    // ─────────────────────────────────────────────
    // Factory helper — always use this to record ledger entries
    // ─────────────────────────────────────────────

    /**
     * Record an inventory transaction and return the new entry.
     *
     * @param  Material            $material
     * @param  string              $type       'addition' | 'deduction' | 'adjustment'
     * @param  float               $qty        Always a positive value
     * @param  float               $balanceBefore
     * @param  User                $performedBy
     * @param  Model|null          $reference  The model that triggered this (e.g. InventoryRequisition)
     * @param  string|null         $notes
     */
    public static function record(
        Material $material,
        string   $type,
        float    $qty,
        float    $balanceBefore,
        User     $performedBy,
        ?Model   $reference = null,
        ?string  $notes = null,
    ): self {
        $balanceAfter = match ($type) {
            'addition'   => $balanceBefore + $qty,
            'deduction'  => $balanceBefore - $qty,
            'adjustment' => $qty, // for adjustments, quantity IS the new balance
        };

        return static::create([
            'material_id'      => $material->id,
            'transaction_type' => $type,
            'quantity'         => $qty,
            'balance_before'   => $balanceBefore,
            'balance_after'    => $balanceAfter,
            'reference_type'   => $reference ? get_class($reference) : null,
            'reference_id'     => $reference?->getKey(),
            'performed_by'     => $performedBy->id,
            'notes'            => $notes,
            'created_at'       => now(),
        ]);
    }

    // ─────────────────────────────────────────────
    // Display helpers
    // ─────────────────────────────────────────────

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'addition'   => 'Stock In',
            'deduction'  => 'Stock Out',
            'adjustment' => 'Adjustment',
            default      => $type,
        };
    }

    public static function typeColor(string $type): string
    {
        return match ($type) {
            'addition'   => 'success',
            'deduction'  => 'danger',
            'adjustment' => 'warning',
            default      => 'gray',
        };
    }
}
