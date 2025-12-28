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
        Schema::table('imprest_assignments', function (Blueprint $table) {
            $table->enum('payment_method', ['bank_transfer', 'mobile_money', 'cash'])->nullable()->after('receipt_submitted_at');
            $table->date('payment_date')->nullable()->after('payment_method');
            $table->unsignedBigInteger('bank_account_id')->nullable()->after('payment_date');
            $table->string('bank_name')->nullable()->after('bank_account_id');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('payment_reference')->nullable()->after('account_number');
            $table->text('payment_notes')->nullable()->after('payment_reference');
            $table->decimal('paid_amount', 15, 2)->nullable()->after('payment_notes');
            $table->timestamp('paid_at')->nullable()->after('paid_amount');
            $table->unsignedBigInteger('paid_by')->nullable()->after('paid_at');
            
            // Foreign keys
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imprest_assignments', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropForeign(['paid_by']);
            $table->dropColumn([
                'payment_method',
                'payment_date',
                'bank_account_id',
                'bank_name',
                'account_number',
                'payment_reference',
                'payment_notes',
                'paid_amount',
                'paid_at',
                'paid_by'
            ]);
        });
    }
};
