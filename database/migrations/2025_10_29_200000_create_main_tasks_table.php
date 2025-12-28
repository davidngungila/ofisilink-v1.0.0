<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('main_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('timeframe')->nullable();
            $table->unsignedBigInteger('team_leader_id');
            $table->enum('status', ['planning', 'in_progress', 'completed', 'delayed'])->default('planning');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('team_leader_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('main_tasks');
    }
};







