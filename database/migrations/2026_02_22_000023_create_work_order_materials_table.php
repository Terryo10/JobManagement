<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity_used', 10, 2);
            $table->decimal('unit_cost_at_time', 10, 2)->nullable(); // cost locked at time of use
            $table->foreignId('logged_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('logged_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('work_order_id');
            $table->index('material_id');
            $table->index('logged_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_materials');
    }
};
