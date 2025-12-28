<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_work_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('company_name');
            $table->string('position');
            $table->string('department')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null if current position
            $table->boolean('is_current')->default(false);
            $table->string('employment_type')->nullable(); // Full-time, Part-time, Contract, etc.
            $table->text('job_description')->nullable();
            $table->text('achievements')->nullable();
            $table->string('reason_for_leaving')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_contact')->nullable();
            $table->string('location')->nullable();
            $table->decimal('salary', 15, 2)->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_work_history');
    }
};
