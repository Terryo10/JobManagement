<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type', 50)->default('client_report'); // client_report, internal_report, proposal_analysis, growth_strategy
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->longText('content')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('draft'); // draft, final, submitted
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('client_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_reports');
    }
};
