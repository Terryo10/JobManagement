<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique()->index();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('category', 50); // media, civil_works, energy, warehouse
            $table->string('status', 50)->default('pending')->index(); // pending, in_progress, on_hold, completed, cancelled
            $table->string('priority', 20)->default('normal')->index(); // low, normal, high, urgent
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->default(0.00);
            $table->unsignedTinyInteger('budget_alert_threshold')->default(80);
            $table->foreignId('assigned_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->json('details')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('client_id');
            $table->index('assigned_department_id');
            $table->index('category');
            $table->fullText('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
