<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_capacity_settings', function (Blueprint $table) {
            $table->id();
            $table->string('role_name', 100)->unique(); // Spatie role name
            $table->unsignedTinyInteger('max_concurrent_tasks')->default(5);
            $table->unsignedTinyInteger('max_weekly_hours')->default(40);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_capacity_settings');
    }
};
