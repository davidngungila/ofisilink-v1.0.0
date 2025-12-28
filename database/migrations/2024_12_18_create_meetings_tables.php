<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Meeting Categories
        Schema::create('meeting_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // Meetings
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->date('meeting_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('venue');
            $table->enum('meeting_type', ['physical', 'virtual', 'hybrid'])->default('physical');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'completed', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('minutes_finalized_at')->nullable();
            $table->unsignedBigInteger('minutes_finalized_by')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('meeting_categories')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users');
        });

        // Meeting Participants
        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('participant_type', ['staff', 'external'])->default('staff');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('institution')->nullable();
            $table->boolean('attended')->default(false);
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Meeting Agendas
        Schema::create('meeting_agendas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->string('title');
            $table->string('duration')->nullable();
            $table->unsignedBigInteger('presenter_id')->nullable();
            $table->text('documents')->nullable();
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->text('discussion_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            $table->foreign('presenter_id')->references('id')->on('users')->onDelete('set null');
        });

        // Meeting Minutes
        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id')->unique();
            $table->text('aob')->nullable();
            $table->time('closing_time')->nullable();
            $table->text('closing_remarks')->nullable();
            $table->date('next_meeting_date')->nullable();
            $table->time('next_meeting_time')->nullable();
            $table->string('next_meeting_venue')->nullable();
            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            $table->foreign('prepared_by')->references('id')->on('users')->onDelete('set null');
        });

        // Meeting Previous Actions (for minutes)
        Schema::create('meeting_previous_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->text('description');
            $table->enum('status', ['done', 'in_progress', 'pending', 'deferred'])->default('pending');
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            $table->foreign('responsible_id')->references('id')->on('users')->onDelete('set null');
        });

        // Meeting Action Items
        Schema::create('meeting_action_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->text('description');
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->date('deadline')->nullable();
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'in_progress', 'done', 'deferred'])->default('pending');
            $table->text('completion_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            $table->foreign('responsible_id')->references('id')->on('users')->onDelete('set null');
        });

        // Meeting Activity Log
        Schema::create('meeting_activity_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->text('description');
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_activity_log');
        Schema::dropIfExists('meeting_action_items');
        Schema::dropIfExists('meeting_previous_actions');
        Schema::dropIfExists('meeting_minutes');
        Schema::dropIfExists('meeting_agendas');
        Schema::dropIfExists('meeting_participants');
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('meeting_categories');
    }
};


