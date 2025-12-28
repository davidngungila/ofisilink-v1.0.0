<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meeting_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('reference_code')->nullable()->unique();
            $table->foreignId('category_id')->nullable()->constrained('meeting_categories')->nullOnDelete();
            $table->date('meeting_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->string('meeting_mode')->default('in_person'); // in_person, virtual, hybrid
            $table->string('virtual_link')->nullable();
            $table->string('status')->default('draft'); // draft, pending_hod, pending_ceo, approved, rejected, cancelled
            $table->string('approval_target')->default('HOD');
            $table->text('approval_notes')->nullable();
            $table->text('agenda_overview')->nullable();
            $table->boolean('previous_actions_included')->default(false);
            $table->string('minutes_status')->default('not_started'); // not_started, in_progress, completed
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->enum('participant_type', ['staff', 'external']);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('institution')->nullable();
            $table->string('role')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('attendance_status')->default('invited'); // invited, confirmed, declined, attended, absent
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });

        Schema::create('meeting_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('presenter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('pending'); // pending, in_progress, done
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->unique()->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft'); // draft, final
            $table->text('summary')->nullable();
            $table->date('next_meeting_date')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('meeting_minute_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_minute_id')->constrained('meeting_minutes')->cascadeOnDelete();
            $table->foreignId('agenda_id')->nullable()->constrained('meeting_agendas')->nullOnDelete();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('source_meeting_id')->nullable()->constrained('meetings')->nullOnDelete();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->text('decisions')->nullable();
            $table->boolean('action_required')->default(false);
            $table->date('due_date')->nullable();
            $table->string('status')->default('open'); // open, in_progress, done
            $table->boolean('from_previous')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
    * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('meeting_minute_items');
        Schema::dropIfExists('meeting_minutes');
        Schema::dropIfExists('meeting_agendas');
        Schema::dropIfExists('meeting_participants');
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('meeting_categories');
    }
};

