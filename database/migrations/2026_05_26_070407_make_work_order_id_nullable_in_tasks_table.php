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
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('work_order_id')->nullable()->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('work_order_id')->references('id')->on('work_orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('work_order_id')->nullable(false)->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('work_order_id')->references('id')->on('work_orders')->cascadeOnDelete();
        });
    }
};
