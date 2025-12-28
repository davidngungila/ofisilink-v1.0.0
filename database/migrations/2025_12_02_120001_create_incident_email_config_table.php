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
        Schema::create('incident_email_config', function (Blueprint $table) {
            $table->id();
            $table->string('email_address')->unique();
            $table->enum('protocol', ['imap', 'pop3'])->default('imap');
            $table->string('host');
            $table->integer('port');
            $table->string('username');
            $table->string('password'); // Encrypted
            $table->boolean('ssl_enabled')->default(true);
            $table->string('folder')->default('INBOX'); // For IMAP
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->text('sync_settings')->nullable(); // JSON for additional settings
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_email_config');
    }
};




