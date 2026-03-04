<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 50)->default('collaborator'); // collaborator, lead, supervisor
            $table->timestamp('added_at')->useCurrent();
            $table->unique(['work_order_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_collaborators');
    }
};
