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
        // First, update the enum to include new return statuses
        DB::statement("ALTER TABLE `rack_file_requests` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'return_pending', 'return_approved', 'return_rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Revert the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE `rack_file_requests` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};


