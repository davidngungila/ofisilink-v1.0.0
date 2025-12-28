<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imprest_approval_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('imprest_request_id');
            $table->string('action'); // approve, reject, adjust_amount, comment
            $table->enum('level', ['hod', 'ceo', 'accountant', 'system'])->default('hod');
            $table->unsignedBigInteger('action_by');
            $table->text('comments')->nullable();
            $table->decimal('old_amount', 15, 2)->nullable();
            $table->decimal('new_amount', 15, 2)->nullable();
            $table->json('metadata')->nullable(); // Store additional data
            $table->timestamp('action_at');
            $table->timestamps();
            
            $table->foreign('imprest_request_id')->references('id')->on('imprest_requests')->onDelete('cascade');
            $table->foreign('action_by')->references('id')->on('users');
            $table->index(['imprest_request_id', 'action_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imprest_approval_history');
    }
};






