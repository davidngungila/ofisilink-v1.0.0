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
        Schema::table('petty_cash_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('voucher_number')->nullable()->after('user_id');
            $table->date('voucher_date')->nullable()->after('voucher_number');
            $table->text('description')->nullable()->after('voucher_date');
            $table->decimal('total_amount', 10, 2)->default(0)->after('description');
            $table->unsignedBigInteger('approved_by')->nullable()->after('total_amount');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('comments')->nullable()->after('approved_at');
            $table->boolean('receipts_attached')->default(false)->after('comments');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash_vouchers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'user_id',
                'voucher_number',
                'voucher_date',
                'description',
                'total_amount',
                'approved_by',
                'approved_at',
                'comments',
                'receipts_attached'
            ]);
        });
    }
};