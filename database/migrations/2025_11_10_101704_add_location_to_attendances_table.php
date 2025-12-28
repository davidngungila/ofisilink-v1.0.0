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
        Schema::table('attendances', function (Blueprint $table) {
            // Add location_id if it doesn't exist
            if (!Schema::hasColumn('attendances', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('employee_id');
            }
            
            // Add attendance_device_id for foreign key (keeping existing device_id as string)
            if (!Schema::hasColumn('attendances', 'attendance_device_id')) {
                $table->unsignedBigInteger('attendance_device_id')->nullable()->after('device_type');
            }
            
            // Add schedule_id if it doesn't exist
            if (!Schema::hasColumn('attendances', 'schedule_id')) {
                $table->unsignedBigInteger('schedule_id')->nullable()->after('location_id');
            }
        });
        
        // Add foreign keys and indexes separately to avoid issues
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'location_id')) {
                $table->foreign('location_id')->references('id')->on('attendance_locations')->onDelete('set null');
                $table->index('location_id');
            }
            
            if (Schema::hasColumn('attendances', 'attendance_device_id')) {
                $table->foreign('attendance_device_id')->references('id')->on('attendance_devices')->onDelete('set null');
                $table->index('attendance_device_id');
            }
            
            if (Schema::hasColumn('attendances', 'schedule_id')) {
                $table->foreign('schedule_id')->references('id')->on('work_schedules')->onDelete('set null');
                $table->index('schedule_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'location_id')) {
                $table->dropForeign(['location_id']);
                $table->dropIndex(['location_id']);
                $table->dropColumn('location_id');
            }
            
            if (Schema::hasColumn('attendances', 'attendance_device_id')) {
                $table->dropForeign(['attendance_device_id']);
                $table->dropIndex(['attendance_device_id']);
                $table->dropColumn('attendance_device_id');
            }
            
            if (Schema::hasColumn('attendances', 'schedule_id')) {
                $table->dropForeign(['schedule_id']);
                $table->dropIndex(['schedule_id']);
                $table->dropColumn('schedule_id');
            }
        });
    }
};
