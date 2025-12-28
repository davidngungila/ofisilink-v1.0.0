<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            // Add max_days_per_year if it doesn't exist
            if (!Schema::hasColumn('leave_types', 'max_days_per_year')) {
                $table->integer('max_days_per_year')->default(28)->after('description');
                
                // Copy data from max_days if it exists
                if (Schema::hasColumn('leave_types', 'max_days')) {
                    DB::statement('UPDATE leave_types SET max_days_per_year = max_days WHERE max_days_per_year IS NULL');
                } else {
                    // Set default value if max_days doesn't exist
                    DB::statement('UPDATE leave_types SET max_days_per_year = 28 WHERE max_days_per_year IS NULL');
                }
            }
            
            // Also ensure is_paid exists
            if (!Schema::hasColumn('leave_types', 'is_paid')) {
                $table->boolean('is_paid')->default(true)->after('requires_approval');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            if (Schema::hasColumn('leave_types', 'max_days_per_year')) {
                $table->dropColumn('max_days_per_year');
            }
            if (Schema::hasColumn('leave_types', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
        });
    }
};
