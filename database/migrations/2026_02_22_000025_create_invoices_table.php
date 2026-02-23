<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique()->index(); // INV-2026-0018
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->string('status', 30)->default('draft')->index(); // draft, sent, paid, overdue, cancelled
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('client_id');
            $table->index('work_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
