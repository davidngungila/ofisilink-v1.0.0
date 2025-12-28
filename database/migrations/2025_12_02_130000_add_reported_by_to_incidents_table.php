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
            if (!Schema::hasColumn('incidents', 'reported_by')) {
                $table->unsignedBigInteger('reported_by')->nullable()->after('reported_by_phone');
                $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
                $table->index('reported_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (Schema::hasColumn('incidents', 'reported_by')) {
                $table->dropForeign(['reported_by']);
                $table->dropIndex(['reported_by']);
                $table->dropColumn('reported_by');
            }
        });
    }
};




