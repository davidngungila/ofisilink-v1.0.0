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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('salary', 15, 2);
            $table->string('employment_type')->default('permanent'); // permanent, contract, temporary
            $table->string('position')->nullable();
            $table->string('heslb_number')->nullable();
            $table->boolean('has_student_loan')->default(false);
            $table->string('tin_number')->nullable();
            $table->string('nssf_number')->nullable();
            $table->string('nhif_number')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('user_id');
            $table->index(['employment_type', 'hire_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};