<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            // Add is_paid if it doesn't exist
            if (!Schema::hasColumn('leave_types', 'is_paid')) {
                $table->boolean('is_paid')->default(true)->after('requires_approval');
            }
            
            // Add max_days_per_year if it doesn't exist (keep max_days for backward compatibility)
            if (!Schema::hasColumn('leave_types', 'max_days_per_year')) {
                $table->integer('max_days_per_year')->nullable()->after('is_paid');
            }
        });
        
        // Copy data from max_days if it exists (do this after column is created)
        if (Schema::hasColumn('leave_types', 'max_days') && Schema::hasColumn('leave_types', 'max_days_per_year')) {
            DB::statement('UPDATE leave_types SET max_days_per_year = max_days WHERE max_days_per_year IS NULL AND max_days IS NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            if (Schema::hasColumn('leave_types', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
            if (Schema::hasColumn('leave_types', 'max_days_per_year')) {
                $table->dropColumn('max_days_per_year');
            }
        });
    }
};

