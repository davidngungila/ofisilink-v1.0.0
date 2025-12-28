<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to modify the ENUM column
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Draft', 'Pending for Approval', 'Approved', 'Rejected', 'Sent', 'Partially Paid', 'Paid', 'Cancelled', 'Overdue') DEFAULT 'Draft'");
    }

    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Draft', 'Sent', 'Partially Paid', 'Paid', 'Cancelled', 'Overdue') DEFAULT 'Draft'");
    }
};



