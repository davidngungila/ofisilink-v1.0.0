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
            // Add indexes for performance optimization
            $table->index('status', 'idx_pcv_status');
            $table->index('created_by', 'idx_pcv_created_by');
            $table->index('created_at', 'idx_pcv_created_at');
            $table->index(['status', 'created_at'], 'idx_pcv_status_created');
            $table->index(['status', 'date'], 'idx_pcv_status_date');
            $table->index('date', 'idx_pcv_date');
            $table->index('amount', 'idx_pcv_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash_vouchers', function (Blueprint $table) {
            $table->dropIndex('idx_pcv_status');
            $table->dropIndex('idx_pcv_created_by');
            $table->dropIndex('idx_pcv_created_at');
            $table->dropIndex('idx_pcv_status_created');
            $table->dropIndex('idx_pcv_status_date');
            $table->dropIndex('idx_pcv_date');
            $table->dropIndex('idx_pcv_amount');
        });
    }
};






