<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50)->default('proposal'); // pitch, proposal, quotation
            $table->string('status', 30)->default('draft'); // draft, submitted, accepted, rejected
            $table->decimal('value', 12, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->date('submitted_at')->nullable();
            $table->date('valid_until')->nullable();
            $table->longText('content')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('client_id');
            $table->index('lead_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
