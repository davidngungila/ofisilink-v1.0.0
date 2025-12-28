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
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Add name column if it doesn't exist
            if (!Schema::hasColumn('bank_accounts', 'name')) {
                $table->string('name')->nullable()->after('user_id');
            }
            
            // Add balance column if it doesn't exist
            if (!Schema::hasColumn('bank_accounts', 'balance')) {
                $table->decimal('balance', 15, 2)->default(0)->after('swift_code');
            }
            
            // Add account_id column if it doesn't exist (Chart of Account reference)
            if (!Schema::hasColumn('bank_accounts', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->after('balance');
                $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('bank_accounts', 'account_id')) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            }
            if (Schema::hasColumn('bank_accounts', 'balance')) {
                $table->dropColumn('balance');
            }
            if (Schema::hasColumn('bank_accounts', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
