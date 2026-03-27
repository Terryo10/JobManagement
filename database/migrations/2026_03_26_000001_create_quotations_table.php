<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number', 50)->unique()->index();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->default('draft')->index();
            // draft, sent, accepted, rejected, expired, converted
            $table->string('currency', 10)->default('USD');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
