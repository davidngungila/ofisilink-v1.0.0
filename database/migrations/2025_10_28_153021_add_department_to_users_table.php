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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('primary_department_id')->nullable()->after('email_verified_at');
            $table->string('employee_id')->unique()->nullable()->after('primary_department_id');
            $table->string('phone')->nullable()->after('employee_id');
            $table->date('hire_date')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('hire_date');
            
            $table->foreign('primary_department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['primary_department_id']);
            $table->dropColumn(['primary_department_id', 'employee_id', 'phone', 'hire_date', 'is_active']);
        });
    }
};
