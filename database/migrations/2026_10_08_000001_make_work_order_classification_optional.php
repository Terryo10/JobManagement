<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->change();
            $table->string('priority', 20)->nullable()->default(null)->change();
            $table->unsignedTinyInteger('budget_alert_threshold')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        DB::table('work_orders')->whereNull('category')->update(['category' => 'media']);
        DB::table('work_orders')->whereNull('priority')->update(['priority' => 'normal']);
        DB::table('work_orders')->whereNull('budget_alert_threshold')->update(['budget_alert_threshold' => 80]);

        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('category', 50)->default('media')->change();
            $table->string('priority', 20)->default('normal')->change();
            $table->unsignedTinyInteger('budget_alert_threshold')->default(80)->change();
        });
    }
};
