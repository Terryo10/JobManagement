<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_research', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 50)->default('trend'); // trend, competitor, opportunity, industry_report
            $table->text('summary')->nullable();
            $table->string('source')->nullable();
            $table->longText('findings')->nullable();
            $table->foreignId('researched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('research_date')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('researched_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_research');
    }
};
