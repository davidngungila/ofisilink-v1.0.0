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
            if (!Schema::hasColumn('incidents', 'category')) {
                $table->enum('category', ['technical', 'hr', 'facilities', 'security', 'other'])->default('technical')->after('priority');
            }
            if (!Schema::hasColumn('incidents', 'source')) {
                $table->string('source')->default('manual')->after('status');
            }
            if (!Schema::hasColumn('incidents', 'email_message_id')) {
                $table->string('email_message_id')->nullable()->after('source');
            }
            if (!Schema::hasColumn('incidents', 'email_thread_id')) {
                $table->string('email_thread_id')->nullable()->after('email_message_id');
            }
            if (!Schema::hasColumn('incidents', 'email_received_at')) {
                $table->timestamp('email_received_at')->nullable()->after('email_thread_id');
            }
            if (!Schema::hasColumn('incidents', 'attachments')) {
                $table->json('attachments')->nullable()->after('email_received_at');
            }
            if (!Schema::hasColumn('incidents', 'custom_fields')) {
                $table->json('custom_fields')->nullable()->after('attachments');
            }
            if (!Schema::hasColumn('incidents', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('custom_fields');
            }
            if (!Schema::hasColumn('incidents', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (Schema::hasColumn('incidents', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('incidents', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('incidents', 'email_message_id')) {
                $table->dropColumn('email_message_id');
            }
            if (Schema::hasColumn('incidents', 'email_thread_id')) {
                $table->dropColumn('email_thread_id');
            }
            if (Schema::hasColumn('incidents', 'email_received_at')) {
                $table->dropColumn('email_received_at');
            }
            if (Schema::hasColumn('incidents', 'attachments')) {
                $table->dropColumn('attachments');
            }
            if (Schema::hasColumn('incidents', 'custom_fields')) {
                $table->dropColumn('custom_fields');
            }
            if (Schema::hasColumn('incidents', 'internal_notes')) {
                $table->dropColumn('internal_notes');
            }
            if (Schema::hasColumn('incidents', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
        });
    }
};
