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
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['user_id']);
        });
        
        // Modify the column to be nullable
        DB::statement('ALTER TABLE bank_accounts MODIFY user_id BIGINT UNSIGNED NULL');
        
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Re-add the foreign key constraint with onDelete('set null') for nullable
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['user_id']);
        });
        
        // Set user_id to NOT NULL (this will fail if there are NULL values)
        DB::statement('ALTER TABLE bank_accounts MODIFY user_id BIGINT UNSIGNED NOT NULL');
        
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Re-add the foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
