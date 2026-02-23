<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('approvable_type'); // App\Models\Invoice or App\Models\Expense
            $table->unsignedBigInteger('approvable_id');
            $table->string('status', 30)->default('pending')->index(); // pending, approved, rejected
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id']);
            $table->index('requested_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_approvals');
    }
};
