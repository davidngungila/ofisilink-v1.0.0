<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('main_responsibilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // owner of responsibility (staff)
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('frequency', ['daily','weekly','monthly'])->default('monthly');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft','pending_approval','approved','active','archived'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('sub_responsibilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('main_responsibility_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('main_responsibility_id')->references('id')->on('main_responsibilities')->onDelete('cascade');
        });

        Schema::create('progress_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_responsibility_id');
            $table->unsignedBigInteger('user_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->text('content'); // textual report
            $table->enum('status', ['Pending','Approved','Rejected'])->default('Pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approver_comments')->nullable();
            $table->timestamps();

            $table->foreign('sub_responsibility_id')->references('id')->on('sub_responsibilities')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_reports');
        Schema::dropIfExists('sub_responsibilities');
        Schema::dropIfExists('main_responsibilities');
    }
};








