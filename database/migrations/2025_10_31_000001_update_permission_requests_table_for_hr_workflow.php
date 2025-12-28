<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permission_requests', function (Blueprint $table) {
            // Update status enum to include HR workflow stages
            // Note: This requires recreating the column
            $table->dropColumn('status');
        });
        
        Schema::table('permission_requests', function (Blueprint $table) {
            $table->enum('status', [
                'pending_hr', 
                'pending_hod', 
                'pending_hr_final', 
                'approved', 
                'rejected', 
                'in_progress',
                'return_pending',
                'return_rejected',
                'completed'
            ])->default('pending_hr')->after('reason_description');
            
            // Add HR review fields
            $table->timestamp('hr_initial_reviewed')->nullable()->after('status');
            $table->unsignedBigInteger('hr_initial_reviewed_by')->nullable()->after('hr_initial_reviewed');
            $table->text('hr_initial_comments')->nullable()->after('hr_initial_reviewed_by');
            
            // Rename existing HOD fields for clarity
            $table->unsignedBigInteger('hod_reviewed_by')->nullable()->after('hod_reviewed');
            
            // Add HR final approval fields
            $table->timestamp('hr_final_reviewed')->nullable()->after('hod_return_reviewed');
            $table->unsignedBigInteger('hr_final_reviewed_by')->nullable()->after('hr_final_reviewed');
            $table->text('hr_final_comments')->nullable()->after('hr_final_reviewed_by');
            
            // Rename return confirmed to return_submitted
            $table->timestamp('return_submitted_at')->nullable()->after('return_datetime');
            
            // Foreign keys
            $table->foreign('hr_initial_reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('hod_reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('hr_final_reviewed_by')->references('id')->on('users')->onDelete('set null');
            
            // Drop CEO fields as they're not needed in HR workflow
            $table->dropColumn(['ceo_reviewed', 'ceo_comments']);
        });
    }

    public function down(): void
    {
        Schema::table('permission_requests', function (Blueprint $table) {
            $table->dropForeign(['hr_initial_reviewed_by']);
            $table->dropForeign(['hod_reviewed_by']);
            $table->dropForeign(['hr_final_reviewed_by']);
            
            $table->dropColumn([
                'status',
                'hr_initial_reviewed',
                'hr_initial_reviewed_by',
                'hr_initial_comments',
                'hod_reviewed_by',
                'hr_final_reviewed',
                'hr_final_reviewed_by',
                'hr_final_comments',
                'return_submitted_at'
            ]);
            
            $table->enum('status', ['pending', 'hod_approved', 'approved', 'rejected', 'return_pending', 'return_rejected', 'completed'])->default('pending');
            $table->timestamp('ceo_reviewed')->nullable();
            $table->text('ceo_comments')->nullable();
        });
    }
};







