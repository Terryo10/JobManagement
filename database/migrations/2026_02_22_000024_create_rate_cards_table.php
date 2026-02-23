<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_cards', function (Blueprint $table) {
            $table->id();
            $table->string('service_type'); // e.g. Billboard Installation, Civil Excavation
            $table->string('category', 50)->index(); // media, civil_works, energy
            $table->string('unit', 50); // per m², per hour, per unit
            $table->decimal('rate', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->date('effective_from')->index();
            $table->date('effective_to')->nullable(); // NULL = currently active
            $table->tinyInteger('is_active')->default(1)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_cards');
    }
};
