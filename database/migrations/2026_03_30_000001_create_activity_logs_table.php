<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30);          // deleted, restored, deletion_requested, deletion_approved, deletion_rejected
            $table->string('subject_type');          // e.g. App\Models\WorkOrder
            $table->unsignedBigInteger('subject_id');
            $table->string('subject_label');         // e.g. "Work Order #WO-2026-001"
            $table->json('properties')->nullable();  // compact key identifiers only
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
