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
        if (!Schema::hasTable('incidents')) {
            return; // Table doesn't exist, skip
        }

        // Handle reporter_name column
        if (Schema::hasColumn('incidents', 'reported_by_name') && !Schema::hasColumn('incidents', 'reporter_name')) {
            // Rename using raw SQL (MySQL/MariaDB)
            DB::statement('ALTER TABLE incidents CHANGE COLUMN reported_by_name reporter_name VARCHAR(255) NULL');
        } elseif (!Schema::hasColumn('incidents', 'reporter_name')) {
            // Add new column
            Schema::table('incidents', function (Blueprint $table) {
                $table->string('reporter_name')->nullable()->after('reported_by');
            });
        }
        
        // Handle reporter_email column
        if (Schema::hasColumn('incidents', 'reported_by_email') && !Schema::hasColumn('incidents', 'reporter_email')) {
            // Rename using raw SQL
            DB::statement('ALTER TABLE incidents CHANGE COLUMN reported_by_email reporter_email VARCHAR(255) NULL');
        } elseif (!Schema::hasColumn('incidents', 'reporter_email')) {
            // Add new column
            Schema::table('incidents', function (Blueprint $table) {
                $table->string('reporter_email')->nullable()->after('reporter_name');
            });
        }
        
        // Handle reporter_phone column
        if (Schema::hasColumn('incidents', 'reported_by_phone') && !Schema::hasColumn('incidents', 'reporter_phone')) {
            // Rename using raw SQL
            DB::statement('ALTER TABLE incidents CHANGE COLUMN reported_by_phone reporter_phone VARCHAR(255) NULL');
        } elseif (!Schema::hasColumn('incidents', 'reporter_phone')) {
            // Add new column
            Schema::table('incidents', function (Blueprint $table) {
                $table->string('reporter_phone')->nullable()->after('reporter_email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't reverse this migration as it's a compatibility fix
        // The columns should remain as reporter_name, reporter_email, reporter_phone
    }
};


