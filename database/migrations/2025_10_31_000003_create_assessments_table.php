<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('main_responsibility');
            $table->text('description')->nullable();
            $table->decimal('contribution_percentage', 5, 2); // Percentage for annual performance
            $table->enum('status', ['pending_hod', 'approved', 'rejected'])->default('pending_hod');
            $table->timestamp('hod_approved_at')->nullable();
            $table->unsignedBigInteger('hod_approved_by')->nullable();
            $table->text('hod_comments')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('hod_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['employee_id', 'status']);
        });
        
        Schema::create('assessment_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->string('activity_name');
            $table->text('description')->nullable();
            $table->enum('reporting_frequency', ['daily', 'weekly', 'monthly']);
            $table->decimal('contribution_percentage', 5, 2); // Percentage within the main responsibility
            $table->timestamps();
            
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
        });
        
        Schema::create('assessment_progress_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->date('report_date');
            $table->text('progress_text');
            $table->enum('status', ['pending_approval', 'approved', 'rejected'])->default('pending_approval');
            $table->timestamp('hod_approved_at')->nullable();
            $table->unsignedBigInteger('hod_approved_by')->nullable();
            $table->text('hod_comments')->nullable();
            $table->timestamps();
            
            $table->foreign('activity_id')->references('id')->on('assessment_activities')->onDelete('cascade');
            $table->foreign('hod_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['activity_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_progress_reports');
        Schema::dropIfExists('assessment_activities');
        Schema::dropIfExists('assessments');
    }
};







