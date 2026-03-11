<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_rules', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('applies_to_role');
            $table->string('rule_type', 50)->nullable()->after('rule_key');
            $table->integer('trigger_days')->nullable()->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('notification_rules', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'rule_type', 'trigger_days']);
        });
    }
};
