<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_report_id')->nullable()->constrained()->nullOnDelete();
            $table->string('report_type', 100)->index();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('filters_used')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('status', 30)->default('generating'); // generating, completed, failed
            $table->text('error_message')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index('scheduled_report_id');
            $table->index('generated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_logs');
    }
};
