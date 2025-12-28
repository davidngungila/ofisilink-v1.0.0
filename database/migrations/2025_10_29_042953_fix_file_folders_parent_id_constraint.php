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
        // First, drop the existing foreign key constraint
        Schema::table('file_folders', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });

        // Update the parent_id column to allow NULL for root folders
        Schema::table('file_folders', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->change();
        });

        // Re-add the foreign key constraint with proper handling
        Schema::table('file_folders', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('file_folders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_folders', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
    }
};