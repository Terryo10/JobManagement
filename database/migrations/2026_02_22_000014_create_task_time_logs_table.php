<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamp('started_at')->index();
            $table->timestamp('ended_at')->nullable(); // NULL = timer still running
            $table->unsignedInteger('duration_minutes')->nullable(); // computed on ended_at save
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('task_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_time_logs');
    }
};
