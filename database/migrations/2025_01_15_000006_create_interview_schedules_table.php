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
        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->string('interview_type'); // Written, Oral, Practical
            $table->dateTime('scheduled_at');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('scheduled_by');
            $table->unsignedBigInteger('interviewer_id')->nullable();
            $table->enum('status', ['Scheduled', 'Completed', 'Cancelled', 'Rescheduled'])->default('Scheduled');
            $table->timestamp('completed_at')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('job_applications')->onDelete('cascade');
            $table->foreign('scheduled_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('interviewer_id')->references('id')->on('users')->onDelete('set null');
            $table->index('application_id');
            $table->index('scheduled_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_schedules');
    }
};

