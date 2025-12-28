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
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_id');
            $table->unsignedBigInteger('employee_id');
            
            // Earnings
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_amount', 15, 2)->default(0);
            $table->decimal('bonus_amount', 15, 2)->default(0);
            $table->decimal('allowance_amount', 15, 2)->default(0);
            
            // Deductions
            $table->decimal('deduction_amount', 15, 2)->default(0); // Additional deductions
            $table->decimal('nssf_amount', 15, 2)->default(0);
            $table->decimal('paye_amount', 15, 2)->default(0);
            $table->decimal('nhif_amount', 15, 2)->default(0);
            $table->decimal('heslb_amount', 15, 2)->default(0);
            $table->decimal('wcf_amount', 15, 2)->default(0);
            $table->decimal('sdl_amount', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);
            
            // Employer contributions
            $table->decimal('employer_nssf', 15, 2)->default(0);
            $table->decimal('employer_wcf', 15, 2)->default(0);
            $table->decimal('employer_sdl', 15, 2)->default(0);
            $table->decimal('total_employer_cost', 15, 2)->default(0);
            
            // Final amounts
            $table->decimal('net_salary', 15, 2);
            $table->enum('status', ['processed', 'reviewed', 'approved', 'paid', 'rejected'])->default('processed');
            
            $table->timestamps();

            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('users');
            
            $table->index(['payroll_id', 'employee_id']);
            $table->index(['employee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};