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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->string('leave_location');
            $table->enum('status', [
                'pending_hr_review',
                'pending_hod_approval', 
                'pending_ceo_approval',
                'approved_pending_docs',
                'on_leave',
                'completed',
                'rejected',
                'rejected_for_edit',
                'cancelled'
            ])->default('pending_hr_review');
            
            // Review and approval fields
            $table->text('hr_officer_comments')->nullable();
            $table->text('comments')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            
            // Document processing fields
            $table->string('approval_letter_number')->nullable();
            $table->date('approval_date')->nullable();
            $table->string('leave_certificate_number')->nullable();
            $table->string('fare_certificate_number')->nullable();
            $table->decimal('fare_approved_amount', 10, 2)->default(0);
            $table->string('payment_voucher_number')->nullable();
            $table->date('payment_date')->nullable();
            $table->text('hr_processing_notes')->nullable();
            $table->unsignedBigInteger('documents_processed_by')->nullable();
            $table->timestamp('documents_processed_at')->nullable();
            
            // Return from leave fields
            $table->date('actual_return_date')->nullable();
            $table->enum('health_status', ['excellent', 'good', 'fair', 'poor'])->nullable();
            $table->enum('work_readiness', ['fully_ready', 'partially_ready', 'needs_training', 'not_ready'])->nullable();
            $table->text('return_comments')->nullable();
            $table->string('resumption_certificate_path')->nullable();
            $table->timestamp('return_submitted_at')->nullable();
            
            // Fare information
            $table->decimal('total_fare_approved', 10, 2)->default(0);
            
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('documents_processed_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};