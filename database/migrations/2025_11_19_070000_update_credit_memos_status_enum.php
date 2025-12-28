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
        Schema::table('credit_memos', function (Blueprint $table) {
            // Temporarily change column to string to allow modification
            $table->string('status_temp')->after('reason')->nullable();
        });

        // Copy data
        DB::statement("UPDATE credit_memos SET status_temp = status");

        Schema::table('credit_memos', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('credit_memos', function (Blueprint $table) {
            $table->enum('status', ['Draft', 'Pending for Approval', 'Approved', 'Rejected', 'Posted', 'Cancelled'])
                  ->default('Draft')
                  ->after('reason')
                  ->index();
        });

        // Copy data back, mapping old statuses to new if needed
        DB::statement("UPDATE credit_memos SET status = status_temp");

        Schema::table('credit_memos', function (Blueprint $table) {
            $table->dropColumn('status_temp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_memos', function (Blueprint $table) {
            $table->string('status_temp')->after('reason')->nullable();
        });

        DB::statement("UPDATE credit_memos SET status_temp = status");

        Schema::table('credit_memos', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('credit_memos', function (Blueprint $table) {
            $table->enum('status', ['Draft', 'Posted', 'Cancelled'])
                  ->default('Draft')
                  ->after('reason')
                  ->index();
        });

        DB::statement("UPDATE credit_memos SET status = status_temp");

        Schema::table('credit_memos', function (Blueprint $table) {
            $table->dropColumn('status_temp');
        });
    }
};



