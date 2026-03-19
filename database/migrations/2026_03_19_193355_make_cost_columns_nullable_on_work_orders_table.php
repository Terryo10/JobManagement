<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->decimal('budget', 15, 2)->nullable()->change();
            $table->decimal('actual_cost', 15, 2)->nullable()->change();
            $table->date('start_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->decimal('budget', 15, 2)->nullable(false)->change();
            $table->decimal('actual_cost', 15, 2)->nullable(false)->change();
            $table->date('start_date')->nullable(false)->change();
        });
    }
};
