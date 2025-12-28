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
        Schema::create('attendance_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('location_id')->nullable(); // Location-specific policy
            $table->unsignedBigInteger('department_id')->nullable(); // Department-specific policy
            $table->boolean('require_approval_for_late')->default(false);
            $table->boolean('require_approval_for_early_leave')->default(false);
            $table->boolean('require_approval_for_overtime')->default(false);
            $table->boolean('allow_remote_attendance')->default(false);
            $table->integer('max_remote_days_per_month')->nullable();
            $table->boolean('auto_approve_verified')->default(true); // Auto-approve biometric entries
            $table->boolean('require_photo_for_manual')->default(false);
            $table->boolean('require_location_for_mobile')->default(true);
            $table->integer('max_late_minutes_per_month')->nullable();
            $table->integer('max_early_leave_minutes_per_month')->nullable();
            $table->json('allowed_attendance_methods')->nullable(); // Which methods are allowed
            $table->json('penalty_rules')->nullable(); // Late/absence penalties
            $table->json('reward_rules')->nullable(); // Perfect attendance rewards
            $table->json('notification_settings')->nullable(); // When to send notifications
            $table->json('approval_workflow')->nullable(); // Approval workflow settings
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
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
        Schema::dropIfExists('attendance_policies');
    }
};
