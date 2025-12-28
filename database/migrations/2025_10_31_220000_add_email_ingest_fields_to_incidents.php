<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (!Schema::hasColumn('incidents', 'source')) {
                $table->string('source')->nullable()->after('incident_code'); // email, manual, web
            }
            if (!Schema::hasColumn('incidents', 'raw_email_id')) {
                $table->string('raw_email_id')->nullable()->after('source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (Schema::hasColumn('incidents', 'raw_email_id')) {
                $table->dropColumn('raw_email_id');
            }
            if (Schema::hasColumn('incidents', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};


