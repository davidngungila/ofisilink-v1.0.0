<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add chart_of_account_id to gl_accounts table
        if (Schema::hasTable('gl_accounts') && !Schema::hasColumn('gl_accounts', 'chart_of_account_id')) {
            Schema::table('gl_accounts', function (Blueprint $table) {
                $table->unsignedBigInteger('chart_of_account_id')->nullable()->after('is_active');
                $table->foreign('chart_of_account_id')
                    ->references('id')
                    ->on('chart_of_accounts')
                    ->onDelete('set null');
                $table->index('chart_of_account_id');
            });
        }

        // Add chart_of_account_id to cash_boxes table
        if (Schema::hasTable('cash_boxes') && !Schema::hasColumn('cash_boxes', 'chart_of_account_id')) {
            Schema::table('cash_boxes', function (Blueprint $table) {
                $table->unsignedBigInteger('chart_of_account_id')->nullable()->after('is_active');
                $table->foreign('chart_of_account_id')
                    ->references('id')
                    ->on('chart_of_accounts')
                    ->onDelete('set null');
                $table->index('chart_of_account_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gl_accounts') && Schema::hasColumn('gl_accounts', 'chart_of_account_id')) {
            Schema::table('gl_accounts', function (Blueprint $table) {
                $table->dropForeign(['chart_of_account_id']);
                $table->dropColumn('chart_of_account_id');
            });
        }

        if (Schema::hasTable('cash_boxes') && Schema::hasColumn('cash_boxes', 'chart_of_account_id')) {
            Schema::table('cash_boxes', function (Blueprint $table) {
                $table->dropForeign(['chart_of_account_id']);
                $table->dropColumn('chart_of_account_id');
            });
        }
    }
};



