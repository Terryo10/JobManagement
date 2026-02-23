<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deadline_escalations', function (Blueprint $table) {
            $table->id();
            $table->string('escalatable_type'); // App\Models\WorkOrder or App\Models\Task
            $table->unsignedBigInteger('escalatable_id');
            $table->unsignedTinyInteger('escalation_level')->default(1); // 1=Dept Head, 2=Manager, 3=Admin
            $table->foreignId('escalated_to')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->decimal('overdue_hours_at_escalation', 8, 2);
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamps();

            $table->index(['escalatable_type', 'escalatable_id']);
            $table->index('escalated_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deadline_escalations');
    }
};
