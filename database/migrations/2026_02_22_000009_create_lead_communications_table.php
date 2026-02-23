<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50); // call, email, meeting, visit, note
            $table->text('summary');
            $table->string('outcome', 100)->nullable();
            $table->text('next_action')->nullable();
            $table->date('next_action_date')->nullable();
            $table->timestamps();

            $table->index('lead_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_communications');
    }
};
