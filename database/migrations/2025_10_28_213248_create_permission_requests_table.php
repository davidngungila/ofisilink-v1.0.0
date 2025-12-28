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
        Schema::create('permission_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('time_mode', ['hours', 'days']);
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->enum('reason_type', ['official', 'personal', 'medical', 'emergency', 'other']);
            $table->text('reason_description');
            $table->enum('status', ['pending', 'hod_approved', 'approved', 'rejected', 'return_pending', 'return_rejected', 'completed'])->default('pending');
            
            // HOD review
            $table->timestamp('hod_reviewed')->nullable();
            $table->text('hod_comments')->nullable();
            
            // CEO review
            $table->timestamp('ceo_reviewed')->nullable();
            $table->text('ceo_comments')->nullable();
            
            // Return confirmation
            $table->dateTime('return_datetime')->nullable();
            $table->text('return_remarks')->nullable();
            $table->timestamp('return_confirmed')->nullable();
            
            // HOD return approval
            $table->timestamp('hod_return_reviewed')->nullable();
            $table->text('hod_return_comments')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_requests');
    }
};
