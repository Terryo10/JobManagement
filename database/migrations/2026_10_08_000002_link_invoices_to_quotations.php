<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->unique()->constrained()->restrictOnDelete();
        });

        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->unsignedInteger('year')->primary();
            $table->unsignedBigInteger('last_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_id');
        });
    }
};
