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
            // ZKTeco specific fields
            $table->string('enroll_id')->nullable()->after('user_id');
            $table->timestamp('check_in_time')->nullable()->after('time_in');
            $table->timestamp('check_out_time')->nullable()->after('time_out');
            $table->timestamp('punch_time')->nullable()->after('check_out_time');
            $table->integer('status_code')->nullable()->comment('1=Check In, 0=Check Out')->after('status');
            $table->string('verify_mode')->nullable()->comment('Fingerprint, Card, etc.')->after('status_code');
            $table->string('device_ip')->nullable()->after('device_type');
            
            // Add index for enroll_id lookups
            $table->index('enroll_id');
            $table->index(['enroll_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['enroll_id']);
            $table->dropIndex(['enroll_id', 'attendance_date']);
            $table->dropColumn([
                'enroll_id',
                'check_in_time',
                'check_out_time',
                'punch_time',
                'status_code',
                'verify_mode',
                'device_ip'
            ]);
        });
    }
};










