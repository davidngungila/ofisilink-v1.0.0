<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('user_id');
            $table->date('report_date');
            $table->text('work_description');
            $table->text('next_activities')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('completion_status', ['In Progress', 'Completed', 'Delayed'])->default('In Progress');
            $table->text('reason_if_delayed')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approver_comments')->nullable();
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('task_activities')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_reports');
    }
};







