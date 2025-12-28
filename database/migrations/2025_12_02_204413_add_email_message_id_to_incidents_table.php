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
        Schema::table('incidents', function (Blueprint $table) {
            if (!Schema::hasColumn('incidents', 'email_message_id')) {
                $table->string('email_message_id')->nullable()->after('source');
            }
            if (!Schema::hasColumn('incidents', 'email_thread_id')) {
                $table->string('email_thread_id')->nullable()->after('email_message_id');
            }
            if (!Schema::hasColumn('incidents', 'email_received_at')) {
                $table->timestamp('email_received_at')->nullable()->after('email_thread_id');
            }
            if (!Schema::hasColumn('incidents', 'email_config_id')) {
                $table->unsignedBigInteger('email_config_id')->nullable()->after('email_received_at');
                $table->foreign('email_config_id')->references('id')->on('incident_email_config')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (Schema::hasColumn('incidents', 'email_config_id')) {
                $table->dropForeign(['email_config_id']);
                $table->dropColumn('email_config_id');
            }
            if (Schema::hasColumn('incidents', 'email_received_at')) {
                $table->dropColumn('email_received_at');
            }
            if (Schema::hasColumn('incidents', 'email_thread_id')) {
                $table->dropColumn('email_thread_id');
            }
            if (Schema::hasColumn('incidents', 'email_message_id')) {
                $table->dropColumn('email_message_id');
            }
        });
    }
};
