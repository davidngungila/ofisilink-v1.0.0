<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('heslb_number');
            }
            if (!Schema::hasColumn('employees', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('employees', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            }
            if (!Schema::hasColumn('employees', 'emergency_contact_address')) {
                $table->text('emergency_contact_address')->nullable()->after('emergency_contact_relationship');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'emergency_contact_name')) {
                $table->dropColumn('emergency_contact_name');
            }
            if (Schema::hasColumn('employees', 'emergency_contact_phone')) {
                $table->dropColumn('emergency_contact_phone');
            }
            if (Schema::hasColumn('employees', 'emergency_contact_relationship')) {
                $table->dropColumn('emergency_contact_relationship');
            }
            if (Schema::hasColumn('employees', 'emergency_contact_address')) {
                $table->dropColumn('emergency_contact_address');
            }
        });
    }
};
