<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('serial_number', 100)->nullable()->unique();
            $table->string('category', 100); // Excavation, Lifting, Electrical Testing
            $table->string('division', 50); // civil_works, energy
            $table->string('status', 50)->default('available')->index(); // available, in_use, maintenance, retired
            $table->foreignId('current_work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();
            $table->date('purchase_date')->nullable();
            $table->date('next_maintenance_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('division');
            $table->index('current_work_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
