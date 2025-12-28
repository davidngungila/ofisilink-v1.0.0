<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->string('barcode_number')->nullable()->unique()->after('asset_code');
            $table->index('barcode_number');
        });
    }

    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropIndex(['barcode_number']);
            $table->dropColumn('barcode_number');
        });
    }
};



