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
        Schema::table('incident_email_config', function (Blueprint $table) {
            $table->enum('connection_status', ['connected', 'disconnected', 'failed', 'unknown'])->default('unknown')->after('is_active');
            $table->timestamp('last_connection_test_at')->nullable()->after('connection_status');
            $table->text('connection_error')->nullable()->after('last_connection_test_at');
            $table->integer('sync_count')->default(0)->after('last_sync_at');
            $table->integer('failed_sync_count')->default(0)->after('sync_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_email_config', function (Blueprint $table) {
            $table->dropColumn([
                'connection_status',
                'last_connection_test_at',
                'connection_error',
                'sync_count',
                'failed_sync_count'
            ]);
        });
    }
};


