<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('main_task_id');
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->string('timeframe')->nullable();
            $table->enum('status', ['Not Started', 'In Progress', 'Completed', 'Delayed'])->default('Not Started');
            $table->timestamps();

            $table->foreign('main_task_id')->references('id')->on('main_tasks')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_activities');
    }
};







