<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sick_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('sheet_number')->unique();
            $table->unsignedBigInteger('employee_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->string('medical_document_path');
            $table->enum('status', [
                'pending_hr',
                'pending_hod',
                'approved',
                'rejected',
                'in_progress',
                'return_pending',
                'completed'
            ])->default('pending_hr');
            
            // HR Review
            $table->timestamp('hr_reviewed_at')->nullable();
            $table->unsignedBigInteger('hr_reviewed_by')->nullable();
            $table->text('hr_comments')->nullable();
            
            // HOD Approval
            $table->timestamp('hod_approved_at')->nullable();
            $table->unsignedBigInteger('hod_approved_by')->nullable();
            $table->text('hod_comments')->nullable();
            
            // Return confirmation
            $table->dateTime('return_submitted_at')->nullable();
            $table->text('return_remarks')->nullable();
            
            // HR final verification
            $table->timestamp('hr_final_verified_at')->nullable();
            $table->unsignedBigInteger('hr_final_verified_by')->nullable();
            $table->text('hr_final_comments')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('hr_reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('hod_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('hr_final_verified_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['status', 'created_at']);
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sick_sheets');
    }
};







