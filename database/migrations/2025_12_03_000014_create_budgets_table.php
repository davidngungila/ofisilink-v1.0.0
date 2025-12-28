<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('budget_name');
            $table->enum('budget_type', ['Annual', 'Quarterly', 'Monthly', 'Custom'])->default('Annual');
            $table->year('fiscal_year');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->enum('status', ['Draft', 'Approved', 'Active', 'Closed'])->default('Draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['fiscal_year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};



