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
        Schema::create('employee_salary_deductions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('deduction_type'); // e.g., 'loan', 'advance', 'insurance', 'other'
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('frequency', ['monthly', 'one-time'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null means ongoing deduction
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['employee_id', 'is_active']);
            $table->index(['employee_id', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salary_deductions');
    }
};







