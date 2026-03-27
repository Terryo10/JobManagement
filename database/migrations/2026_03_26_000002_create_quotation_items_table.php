<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->string('description', 255);
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2)->default(0); // quantity × unit_price
            $table->foreignId('rate_card_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('quotation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
