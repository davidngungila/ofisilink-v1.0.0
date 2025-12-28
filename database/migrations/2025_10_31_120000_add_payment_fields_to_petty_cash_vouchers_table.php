<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petty_cash_vouchers', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('paid_by');
            $table->decimal('paid_amount', 15, 2)->nullable()->after('payment_method');
            $table->string('payment_currency', 10)->nullable()->after('paid_amount');
            $table->string('bank_name')->nullable()->after('payment_currency');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('payment_reference')->nullable()->after('account_number');
            $table->text('payment_notes')->nullable()->after('payment_reference');
            $table->string('payment_attachment_path')->nullable()->after('payment_notes');
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash_vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'paid_amount',
                'payment_currency',
                'bank_name',
                'account_number',
                'payment_reference',
                'payment_notes',
                'payment_attachment_path',
            ]);
        });
    }
};


