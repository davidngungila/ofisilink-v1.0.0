<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('incident_updates', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('incident_id');
			$table->unsignedBigInteger('user_id');
			$table->longText('update_text');
			$table->boolean('is_internal_note')->default(false);
			$table->timestamps();

			$table->foreign('incident_id')->references('id')->on('incidents')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('incident_updates');
	}
};









