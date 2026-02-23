<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Weekly Productivity Summary
            $table->string('report_type', 100)->index(); // operational, client, financial, staff, overdue
            $table->json('filters')->nullable();
            $table->string('frequency', 50); // daily, weekly, monthly
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 0=Monday for weekly
            $table->time('time_of_day')->default('08:00:00');
            $table->json('recipients'); // array of user_ids or email addresses
            $table->string('export_format', 20)->default('pdf'); // pdf, excel, csv
            $table->timestamp('last_sent_at')->nullable();
            $table->tinyInteger('is_active')->default(1)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('frequency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
