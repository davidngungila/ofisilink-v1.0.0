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
        if (!Schema::hasTable('incidents')) {
            Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_no')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['new', 'assigned', 'in_progress', 'resolved', 'closed', 'cancelled'])->default('new');
            $table->enum('category', ['technical', 'hr', 'facilities', 'security', 'other'])->default('technical');
            
            // Reporter information
            $table->unsignedBigInteger('reported_by')->nullable(); // User ID if reported by system user
            $table->string('reporter_name')->nullable(); // Name from email or manual entry
            $table->string('reporter_email')->nullable();
            $table->string('reporter_phone')->nullable();
            
            // Assignment information
            $table->unsignedBigInteger('assigned_to')->nullable(); // Staff assigned to resolve
            $table->timestamp('assigned_at')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable(); // HR/HOD who assigned
            
            // Resolution information
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable(); // Who resolved it
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            
            // Email synchronization
            $table->string('source')->default('manual'); // 'manual', 'email', 'api'
            $table->string('email_message_id')->nullable(); // For email sync
            $table->string('email_thread_id')->nullable();
            $table->timestamp('email_received_at')->nullable();
            
            // Attachments
            $table->json('attachments')->nullable(); // Store file paths/names
            
            // Additional metadata
            $table->json('custom_fields')->nullable();
            $table->text('internal_notes')->nullable(); // For HR/HOD notes not visible to reporter
            
            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('status');
            $table->index('priority');
            $table->index('assigned_to');
            $table->index('reported_by');
            $table->index('email_message_id');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};




