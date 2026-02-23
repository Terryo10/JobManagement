<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('notification_type'); // e.g. task_assigned, job_overdue
            $table->tinyInteger('channel_database')->default(1); // In-app Filament bell
            $table->tinyInteger('channel_mail')->default(1);     // Email via Laravel Mail
            $table->tinyInteger('channel_sms')->default(0);      // SMS via Infobip
            $table->timestamps();

            $table->index('user_id');
            $table->unique(['user_id', 'notification_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
