<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique()->index(); // PO-2026-0042
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('status', 50)->default('draft')->index(); // draft, submitted, approved, ordered, delivered, cancelled
            $table->foreignId('ordered_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->date('expected_delivery')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('ordered_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
