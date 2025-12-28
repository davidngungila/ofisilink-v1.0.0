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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('is_active');
            $table->timestamp('blocked_until')->nullable()->after('blocked_at');
            $table->text('block_reason')->nullable()->after('blocked_until');
            $table->unsignedBigInteger('blocked_by')->nullable()->after('block_reason');
            $table->foreign('blocked_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['blocked_by']);
            $table->dropColumn(['blocked_at', 'blocked_until', 'block_reason', 'blocked_by']);
        });
    }
};
