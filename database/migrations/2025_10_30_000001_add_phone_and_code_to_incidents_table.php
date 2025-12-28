<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('incidents', function (Blueprint $table) {
			$table->string('reported_by_phone')->nullable()->after('reported_by_email');
			$table->string('incident_code')->unique()->nullable()->after('id');
		});
	}

	public function down(): void
	{
		Schema::table('incidents', function (Blueprint $table) {
			$table->dropColumn(['reported_by_phone', 'incident_code']);
		});
	}
};







