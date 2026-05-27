<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_briefs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('designer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('draft'); // draft, pending, in_progress, in_review, revision_requested, completed
            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent
            $table->text('objective')->nullable();
            $table->text('target_audience')->nullable();
            $table->text('deliverables')->nullable();
            $table->text('dimensions_specifications')->nullable();
            $table->text('copy_text')->nullable();
            $table->text('branding_guidelines')->nullable();
            $table->text('notes_references')->nullable();
            $table->date('deadline')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('client_id');
            $table->index('work_order_id');
            $table->index('designer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_briefs');
    }
};
