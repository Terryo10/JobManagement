<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_worker_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_worker_id')
                  ->constrained('field_workers')
                  ->cascadeOnDelete();
            $table->foreignId('task_id')
                  ->constrained('tasks')
                  ->cascadeOnDelete();
            $table->foreignId('assigned_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            // A field worker can only be assigned to a given task once.
            $table->unique(['field_worker_id', 'task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_worker_task');
    }
};
