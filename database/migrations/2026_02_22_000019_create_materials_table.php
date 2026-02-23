<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku', 100)->unique()->index();
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable()->index(); // Electrical, Structural, Consumable
            $table->string('unit', 50); // pcs, kg, m, litres
            $table->decimal('minimum_stock_level', 10, 2)->default(0);
            $table->decimal('reorder_quantity', 10, 2)->nullable();
            $table->foreignId('preferred_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->index('preferred_supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
