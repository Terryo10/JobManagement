<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('networking_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 50)->default('networking'); // conference, seminar, trade_show, networking, workshop
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->json('attendees')->nullable();
            $table->text('outcomes')->nullable();
            $table->unsignedInteger('leads_generated')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('start_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('networking_events');
    }
};
