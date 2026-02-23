<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->unique()->constrained()->cascadeOnDelete(); // one row per material
            $table->decimal('current_quantity', 10, 2)->default(0.00);
            $table->timestamp('last_updated');
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
    }
};
