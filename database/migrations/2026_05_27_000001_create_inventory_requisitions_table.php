<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 30)->unique()->index();

            // 'inventory' = draw from existing stock; 'procurement' = need to purchase first
            $table->enum('type', ['inventory', 'procurement'])->default('inventory')->index();

            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->decimal('quantity_requested', 10, 2);
            $table->decimal('quantity_issued', 10, 2)->nullable(); // filled on issue

            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // recipient
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();

            /*
             * Status lifecycle:
             *   inventory type:    pending → approved → issued | rejected
             *   procurement type:  pending → money_approved → money_issued
             *                      → items_purchased → items_received → issued | rejected
             */
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'money_approved',
                'money_issued',
                'items_purchased',
                'items_received',
                'issued',
            ])->default('pending')->index();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // For procurement type: links back to the originating inventory requisition
            $table->foreignId('procurement_requisition_id')
                ->nullable()
                ->constrained('inventory_requisitions')
                ->nullOnDelete();

            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->decimal('estimated_cost', 12, 2)->nullable(); // for procurement type

            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->index(['requested_by', 'status']);
            $table->index(['material_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_requisitions');
    }
};
