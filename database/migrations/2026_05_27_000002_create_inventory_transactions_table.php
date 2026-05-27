<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();

            // 'addition' = stock in, 'deduction' = stock out, 'adjustment' = manual correction
            $table->enum('transaction_type', ['addition', 'deduction', 'adjustment'])->index();

            // Quantity is always stored as a positive number; direction conveyed by transaction_type
            $table->decimal('quantity', 10, 2);

            // Balance snapshots — immutable audit trail
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);

            // Polymorphic reference to whatever caused this transaction
            // e.g. App\Models\InventoryRequisition, or null for manual adjustments
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->index(['reference_type', 'reference_id'], 'inv_tx_reference_index');

            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();

            // Append-only: only created_at, no updated_at
            $table->timestamp('created_at')->useCurrent();

            $table->index(['material_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
