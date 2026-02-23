<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_key', 100)->unique(); // e.g. deadline_warning_hours
            $table->string('label');
            $table->string('value', 255);
            $table->text('description')->nullable();
            $table->string('applies_to_role')->nullable(); // NULL = all roles
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_rules');
    }
};
