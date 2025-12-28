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
        Schema::create('notification_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Provider name (e.g., "Primary SMTP", "Backup SMS Gateway")
            $table->enum('type', ['email', 'sms']); // Provider type
            $table->boolean('is_active')->default(true); // Whether this provider is active
            $table->boolean('is_primary')->default(false); // Whether this is the primary provider
            $table->integer('priority')->default(0); // Priority order (higher = more priority)
            
            // Email-specific fields
            $table->string('mailer_type')->nullable(); // smtp, sendmail, mailgun, ses
            $table->string('mail_host')->nullable();
            $table->integer('mail_port')->nullable();
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_encryption')->nullable(); // tls, ssl
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            
            // SMS-specific fields
            $table->string('sms_username')->nullable();
            $table->string('sms_password')->nullable();
            $table->string('sms_from')->nullable();
            $table->string('sms_url')->nullable();
            
            // Additional settings (JSON)
            $table->json('additional_settings')->nullable();
            
            // Status and metadata
            $table->text('description')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->boolean('last_test_status')->nullable(); // true = success, false = failed
            $table->text('last_test_message')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['type', 'is_active', 'is_primary']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_providers');
    }
};
