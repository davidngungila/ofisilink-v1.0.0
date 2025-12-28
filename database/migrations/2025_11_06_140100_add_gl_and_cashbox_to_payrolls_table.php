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
            $table->unsignedBigInteger('gl_account_id')->nullable()->after('transaction_reference');
            $table->unsignedBigInteger('cash_box_id')->nullable()->after('gl_account_id');
            $table->text('transaction_details')->nullable()->after('cash_box_id');
            
            $table->foreign('gl_account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('cash_box_id')->references('id')->on('cash_boxes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['gl_account_id']);
            $table->dropForeign(['cash_box_id']);
            $table->dropColumn(['gl_account_id', 'cash_box_id', 'transaction_details']);
        });
    }
};
