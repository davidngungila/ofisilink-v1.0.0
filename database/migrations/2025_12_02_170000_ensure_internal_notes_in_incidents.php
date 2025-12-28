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
            if (!Schema::hasColumn('incidents', 'internal_notes')) {
                // Try to find where to place it
                if (Schema::hasColumn('incidents', 'custom_fields')) {
                    $table->text('internal_notes')->nullable()->after('custom_fields');
                } elseif (Schema::hasColumn('incidents', 'attachments')) {
                    $table->text('internal_notes')->nullable()->after('attachments');
                } elseif (Schema::hasColumn('incidents', 'closed_by')) {
                    $table->text('internal_notes')->nullable()->after('closed_by');
                } else {
                    $table->text('internal_notes')->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (Schema::hasColumn('incidents', 'internal_notes')) {
                $table->dropColumn('internal_notes');
            }
        });
    }
};


