<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Drop foreign key constraint first
        Schema::table('file_user_assignments', function (Blueprint $table) {
            $table->dropForeign(['file_id']);
        });
        
        // Drop unique constraint
        Schema::table('file_user_assignments', function (Blueprint $table) {
            $table->dropUnique(['file_id', 'user_id']);
        });
        
        // Make file_id nullable
        DB::statement('ALTER TABLE file_user_assignments MODIFY file_id BIGINT UNSIGNED NULL');
        
        // Re-add foreign key constraint (nullable)
        Schema::table('file_user_assignments', function (Blueprint $table) {
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
        });
        
        // Add folder_id column and constraints
        Schema::table('file_user_assignments', function (Blueprint $table) {
            $table->foreignId('folder_id')->nullable()->after('file_id')->constrained('file_folders')->onDelete('cascade');
            
            // Add unique constraints for both file and folder assignments
            $table->unique(['file_id', 'user_id'], 'file_user_unique');
            $table->unique(['folder_id', 'user_id'], 'folder_user_unique');
        });
    }

    public function down()
    {
        // Drop folder_id foreign key and unique constraints
        Schema::table('file_user_assignments', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropUnique('file_user_unique');
            $table->dropUnique('folder_user_unique');
        });
        
        // Drop folder_id column
        Schema::table('file_user_assignments', function (Blueprint $table) {
            $table->dropColumn('folder_id');
        });
        
        // Drop file_id foreign key
        Schema::table('file_user_assignments', function (Blueprint $table) {
            $table->dropForeign(['file_id']);
        });
        
        // Make file_id required again
        DB::statement('ALTER TABLE file_user_assignments MODIFY file_id BIGINT UNSIGNED NOT NULL');
        
        // Re-add file_id foreign key and unique constraint
        Schema::table('file_user_assignments', function (Blueprint $table) {
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->unique(['file_id', 'user_id']);
        });
    }
};

