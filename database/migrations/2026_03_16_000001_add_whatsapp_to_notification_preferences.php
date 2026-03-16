<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->boolean('channel_whatsapp')->default(false)->after('channel_sms');
            $table->string('phone_number')->nullable()->after('channel_whatsapp')
                ->comment('SMS delivery number (E.164). Falls back to users.phone_number if null.');
            $table->string('whatsapp_number')->nullable()->after('phone_number')
                ->comment('WhatsApp delivery number (E.164). Falls back to phone_number if null.');
            $table->json('quiet_hours')->nullable()->after('whatsapp_number')
                ->comment('{"start":"22:00","end":"07:00","tz":"Africa/Harare"} — blocks SMS/WhatsApp only.');
            $table->string('locale')->default('en')->after('quiet_hours');
        });
    }

    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->dropColumn(['channel_whatsapp', 'phone_number', 'whatsapp_number', 'quiet_hours', 'locale']);
        });
    }
};
