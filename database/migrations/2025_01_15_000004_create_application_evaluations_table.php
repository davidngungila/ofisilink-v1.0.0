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
        Schema::create('application_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id')->unique();
            $table->unsignedBigInteger('interviewer_id');
            $table->decimal('written_score', 5, 2)->nullable();
            $table->decimal('practical_score', 5, 2)->nullable();
            $table->decimal('oral_score', 5, 2)->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('job_applications')->onDelete('cascade');
            $table->foreign('interviewer_id')->references('id')->on('users')->onDelete('cascade');
            
            // Calculate total score as virtual column or use accessor in model
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_evaluations');
    }
};

