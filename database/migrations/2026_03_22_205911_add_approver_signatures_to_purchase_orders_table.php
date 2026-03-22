<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->longText('finance_signature')->nullable()->after('finance_approved_by');
            $table->timestamp('finance_signature_date')->nullable()->after('finance_signature');
            $table->longText('admin_signature')->nullable()->after('finance_signature_date');
            $table->timestamp('admin_signature_date')->nullable()->after('admin_signature');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['finance_signature', 'finance_signature_date', 'admin_signature', 'admin_signature_date']);
        });
    }
};
