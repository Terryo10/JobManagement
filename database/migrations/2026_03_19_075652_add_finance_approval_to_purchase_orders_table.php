<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Make supplier_id nullable (no longer required on the form)
            $table->foreignId('supplier_id')->nullable()->change();

            // New two-stage approval fields
            $table->foreignId('finance_approved_by')
                ->nullable()
                ->after('approved_by')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['finance_approved_by']);
            $table->dropColumn('finance_approved_by');
            $table->foreignId('supplier_id')->nullable(false)->change();
        });
    }
};
