<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('praz_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('title');
            $table->string('tender_number')->nullable();
            $table->string('category', 50)->default('services'); // goods, services, works, consultancy
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('procuring_entity'); // The government body issuing the tender
            $table->longText('description')->nullable();
            $table->dateTime('submission_deadline');
            $table->dateTime('submitted_at')->nullable();
            $table->decimal('value', 14, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->string('status', 30)->default('draft'); // draft, submitted, under_review, approved, rejected, expired
            $table->text('outcome_notes')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('reference_number');
            $table->index('category');
            $table->index('status');
            $table->index('submission_deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('praz_submissions');
    }
};
