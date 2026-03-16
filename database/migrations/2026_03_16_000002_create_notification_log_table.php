<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_log', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->nullableMorphs('notifiable');  // user receiving the notification
            $table->nullableMorphs('subject');     // source model (WO, Task, Expense…)
            $table->string('channel');             // database | mail | sms | whatsapp
            $table->string('status');              // queued | sent | delivered | failed
            $table->string('provider_message_id')->nullable();
            $table->json('payload')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_log');
    }
};
