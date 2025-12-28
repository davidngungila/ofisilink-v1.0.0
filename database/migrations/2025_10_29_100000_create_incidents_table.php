<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('incidents', function (Blueprint $table) {
			$table->id();
			$table->string('subject');
			$table->longText('description');
			$table->string('reported_by_name')->nullable();
			$table->string('reported_by_email')->nullable();
			$table->enum('priority', ['Low','Medium','High','Critical'])->default('Medium');
			$table->enum('status', ['New','Assigned','In Progress','Pending Approval','Resolved'])->default('New');
			$table->date('due_date')->nullable();
			$table->unsignedBigInteger('created_by');
			$table->unsignedBigInteger('assigned_to')->nullable();
			$table->unsignedBigInteger('assigned_by')->nullable();
			$table->longText('resolution_details')->nullable();
			$table->timestamp('resolved_at')->nullable();
			$table->timestamps();

			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
			$table->foreign('assigned_by')->references('id')->on('users')->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('incidents');
	}
};









