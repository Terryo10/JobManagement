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
        Schema::table('praz_submissions', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->string('procuring_entity')->nullable()->change();
            $table->dateTime('submission_deadline')->nullable()->change();
            $table->string('category', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('praz_submissions', function (Blueprint $table) {
            // Reverting back might cause data loss if nulls are present, so usually we just leave it or force change
            $table->string('title')->nullable(false)->change();
            $table->string('procuring_entity')->nullable(false)->change();
            $table->dateTime('submission_deadline')->nullable(false)->change();
            $table->string('category', 50)->default('services')->change();
        });
    }
};
