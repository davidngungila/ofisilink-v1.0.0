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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->enum('status', ['Applied', 'Shortlisted', 'Rejected', 'Interviewing', 'Offer Extended', 'Hired'])->default('Applied');
            $table->unsignedBigInteger('shortlisted_by')->nullable();
            $table->timestamp('shortlisted_at')->nullable();
            $table->timestamp('application_date')->useCurrent();
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('recruitment_jobs')->onDelete('cascade');
            $table->foreign('shortlisted_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
            $table->index('application_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};

