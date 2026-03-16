<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // phone_number already exists — add whatsapp_number beside it
            $table->string('whatsapp_number')->nullable()->after('phone_number');
            // Quiet hours as a user-level JSON setting (not per notification type)
            $table->json('notification_quiet_hours')->nullable()->after('whatsapp_number')
                ->comment('{"start":"22:00","end":"07:00","tz":"Africa/Harare"}');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number', 'notification_quiet_hours']);
        });
    }
};
