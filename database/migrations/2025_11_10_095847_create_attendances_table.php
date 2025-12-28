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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('employee_id')->nullable(); // Reference to employee record
            $table->date('attendance_date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->integer('total_hours')->nullable(); // Total working hours in minutes
            $table->integer('break_duration')->nullable(); // Break duration in minutes
            $table->string('attendance_method')->default('manual'); // manual, biometric, mobile_app, rfid, face_recognition, fingerprint, card_swipe
            $table->string('device_id')->nullable(); // Device identifier for biometric/mobile
            $table->string('device_type')->nullable(); // Device type (biometric_scanner, mobile, etc.)
            $table->string('location')->nullable(); // GPS location for mobile app
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('ip_address')->nullable();
            $table->string('status')->default('present'); // present, absent, late, early_leave, half_day, on_leave
            $table->boolean('is_late')->default(false);
            $table->boolean('is_early_leave')->default(false);
            $table->boolean('is_overtime')->default(false);
            $table->text('notes')->nullable();
            $table->text('remarks')->nullable(); // HR remarks
            $table->unsignedBigInteger('approved_by')->nullable(); // HR/Manager who approved
            $table->timestamp('approved_at')->nullable();
            $table->string('verification_status')->default('pending'); // pending, verified, rejected
            $table->json('metadata')->nullable(); // Additional data (biometric data, etc.)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['user_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
            $table->index('attendance_method');
            $table->unique(['user_id', 'attendance_date']); // One record per user per day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
