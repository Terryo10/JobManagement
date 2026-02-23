<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->string('category', 100); // Labour, Transport, Materials, Equipment
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('USD');
            $table->text('description')->nullable();
            $table->date('expense_date');
            $table->foreignId('submitted_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status', 30)->default('pending')->index(); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('work_order_id');
            $table->index('submitted_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
