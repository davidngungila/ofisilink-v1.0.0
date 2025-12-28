<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('imprest_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no')->unique();
            $table->unsignedBigInteger('accountant_id');
            $table->string('purpose');
            $table->decimal('amount', 15, 2);
            $table->date('expected_return_date')->nullable();
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->text('description')->nullable();
            $table->enum('status', [
                'pending_hod',
                'pending_ceo', 
                'approved',
                'assigned',
                'paid',
                'pending_receipt_verification',
                'completed',
                'rejected'
            ])->default('pending_hod');
            
            // Approval tracking
            $table->timestamp('hod_approved_at')->nullable();
            $table->unsignedBigInteger('hod_approved_by')->nullable();
            $table->timestamp('ceo_approved_at')->nullable();
            $table->unsignedBigInteger('ceo_approved_by')->nullable();
            
            // Payment tracking
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Audit fields
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('accountant_id')->references('id')->on('users');
            $table->foreign('hod_approved_by')->references('id')->on('users');
            $table->foreign('ceo_approved_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imprest_requests');
    }
};
