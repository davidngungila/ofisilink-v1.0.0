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
        Schema::table('leave_recommendations', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_recommendations', 'notes')) {
                $table->text('notes')->nullable()->after('financial_year');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_recommendations', function (Blueprint $table) {
            if (Schema::hasColumn('leave_recommendations', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
