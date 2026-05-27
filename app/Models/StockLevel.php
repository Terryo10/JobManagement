<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class StockLevel extends Model
{
    use LogsActivity;
    public $timestamps = false;

    protected $fillable = ['material_id', 'current_quantity', 'last_updated', 'last_updated_by'];

    protected function casts(): array
    {
        return [
            'last_updated'     => 'datetime',
            'current_quantity' => 'decimal:2',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    // ─────────────────────────────────────────────
    // Stock Mutation Helpers
    // Call these from InventoryService — always inside a DB transaction.
    // ─────────────────────────────────────────────

    /**
     * Deduct stock and write a ledger entry.
     *
     * @param  float         $qty        How much to deduct
     * @param  User          $by         Who is performing the action
     * @param  Model|null    $reference  The model that triggered this (e.g. InventoryRequisition)
     * @param  string|null   $notes
     * @throws \RuntimeException if insufficient stock
     */
    public function deduct(float $qty, User $by, ?Model $reference = null, ?string $notes = null): InventoryTransaction
    {
        // Acquire a row-level lock to prevent concurrent deductions from overdrawing
        $locked = DB::table('stock_levels')->where('id', $this->id)->lockForUpdate()->first();
        $balanceBefore = (float) $locked->current_quantity;

        if ($balanceBefore < $qty) {
            throw new \RuntimeException(
                "Insufficient stock for {$this->material->name}. " .
                "Available: {$balanceBefore}, Requested: {$qty}"
            );
        }

        DB::table('stock_levels')->where('id', $this->id)->update([
            'current_quantity' => $balanceBefore - $qty,
            'last_updated'     => now(),
            'last_updated_by'  => $by->id,
        ]);
        $this->refresh();

        return InventoryTransaction::record(
            material:      $this->material,
            type:          'deduction',
            qty:           $qty,
            balanceBefore: $balanceBefore,
            performedBy:   $by,
            reference:     $reference,
            notes:         $notes,
        );
    }

    /**
     * Add stock and write a ledger entry.
     *
     * @param  float         $qty        How much to add
     * @param  User          $by         Who is performing the action
     * @param  Model|null    $reference  The model that triggered this (e.g. InventoryRequisition)
     * @param  string|null   $notes
     */
    public function add(float $qty, User $by, ?Model $reference = null, ?string $notes = null): InventoryTransaction
    {
        // Acquire a row-level lock to get the true current balance
        $locked = DB::table('stock_levels')->where('id', $this->id)->lockForUpdate()->first();
        $balanceBefore = (float) $locked->current_quantity;

        DB::table('stock_levels')->where('id', $this->id)->update([
            'current_quantity' => $balanceBefore + $qty,
            'last_updated'     => now(),
            'last_updated_by'  => $by->id,
        ]);
        $this->refresh();

        return InventoryTransaction::record(
            material:      $this->material,
            type:          'addition',
            qty:           $qty,
            balanceBefore: $balanceBefore,
            performedBy:   $by,
            reference:     $reference,
            notes:         $notes,
        );
    }
}
