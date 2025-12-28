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
        if (Schema::hasTable('fixed_asset_maintenance')) {
            Schema::rename('fixed_asset_maintenance', 'fixed_asset_maintenances');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('fixed_asset_maintenances')) {
            Schema::rename('fixed_asset_maintenances', 'fixed_asset_maintenance');
        }
    }
};
