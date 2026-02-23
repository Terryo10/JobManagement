<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location_description', 255);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('size', 50)->nullable(); // e.g. 14×48ft
            $table->string('type', 100)->nullable(); // Static, LED, Backlit
            $table->string('status', 50)->default('available')->index(); // available, occupied, maintenance, inactive
            $table->decimal('monthly_rate', 10, 2)->nullable();
            $table->date('next_maintenance_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billboards');
    }
};
