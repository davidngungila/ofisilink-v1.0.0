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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('location_id')->nullable(); // Location-specific schedule
            $table->unsignedBigInteger('department_id')->nullable(); // Department-specific schedule
            $table->time('start_time'); // Work start time
            $table->time('end_time'); // Work end time
            $table->integer('work_hours')->default(8); // Total work hours per day
            $table->integer('break_duration_minutes')->default(60); // Break duration in minutes
            $table->time('break_start_time')->nullable(); // Break start time
            $table->time('break_end_time')->nullable(); // Break end time
            $table->integer('late_tolerance_minutes')->default(15); // Minutes allowed before marked late
            $table->integer('early_leave_tolerance_minutes')->default(15); // Minutes allowed before marked early leave
            $table->integer('overtime_threshold_minutes')->default(30); // Minutes after end time to count as overtime
            $table->json('working_days')->nullable(); // [1,2,3,4,5] for Mon-Fri, etc. (1=Monday, 7=Sunday)
            $table->boolean('is_flexible')->default(false); // Flexible working hours
            $table->time('flexible_start_min')->nullable(); // Earliest start time for flexible
            $table->time('flexible_start_max')->nullable(); // Latest start time for flexible
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->json('holidays')->nullable(); // List of holidays
            $table->json('settings')->nullable(); // Additional schedule settings
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('attendance_locations')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('code');
            $table->index('location_id');
            $table->index('department_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
