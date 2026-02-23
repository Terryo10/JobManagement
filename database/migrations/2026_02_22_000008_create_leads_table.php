<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contact_name');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('company_name')->nullable();
            $table->string('source', 100)->nullable(); // Referral, Website, Cold Call
            $table->string('status', 30)->default('new')->index(); // new, in_progress, converted, lost
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('follow_up_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('assigned_to');
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
