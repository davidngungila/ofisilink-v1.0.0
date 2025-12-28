<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_training', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('training_name');
            $table->string('training_type'); // Internal, External, Online, Workshop, etc.
            $table->string('provider')->nullable();
            $table->string('instructor')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('duration_hours')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Completed', 'In Progress', 'Cancelled', 'Scheduled'])->default('Scheduled');
            $table->string('certificate_number')->nullable();
            $table->string('certificate_file_path')->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            $table->string('sponsor')->nullable(); // Company, Self, Government, etc.
            $table->text('outcomes')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_training');
    }
};
