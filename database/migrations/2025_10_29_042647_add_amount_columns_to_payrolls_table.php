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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('payroll_number')->nullable()->after('user_id');
            $table->date('pay_period_start')->nullable()->after('payroll_number');
            $table->date('pay_period_end')->nullable()->after('pay_period_start');
            $table->decimal('basic_salary', 10, 2)->default(0)->after('pay_period_end');
            $table->decimal('allowances', 10, 2)->default(0)->after('basic_salary');
            $table->decimal('deductions', 10, 2)->default(0)->after('allowances');
            $table->decimal('total_amount', 10, 2)->default(0)->after('deductions');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'payroll_number',
                'pay_period_start',
                'pay_period_end',
                'basic_salary',
                'allowances',
                'deductions',
                'total_amount'
            ]);
        });
    }
};