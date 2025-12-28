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
        Schema::table('imprest_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('imprest_requests', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('paid_at');
            }
        });

        Schema::table('imprest_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('imprest_requests', 'payment_reference')) {
                $table->string('payment_reference', 255)->nullable()->after('payment_method');
            }
        });

        Schema::table('imprest_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('imprest_requests', 'payment_notes')) {
                $table->text('payment_notes')->nullable()->after('payment_reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imprest_requests', function (Blueprint $table) {
            if (Schema::hasColumn('imprest_requests', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('imprest_requests', 'payment_reference')) {
                $table->dropColumn('payment_reference');
            }
            if (Schema::hasColumn('imprest_requests', 'payment_notes')) {
                $table->dropColumn('payment_notes');
            }
        });
    }
};




