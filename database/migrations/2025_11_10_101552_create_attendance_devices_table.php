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
        Schema::create('attendance_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('device_id')->unique(); // Unique device identifier
            $table->string('device_type'); // biometric, rfid, fingerprint, face_recognition, card_swipe
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('serial_number')->nullable();
            $table->unsignedBigInteger('location_id')->nullable(); // Which location this device is at
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->integer('port')->nullable();
            $table->string('connection_type')->default('network'); // network, usb, bluetooth, wifi
            $table->json('connection_config')->nullable(); // Connection settings (API keys, endpoints, etc.)
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_sync_at')->nullable();
            $table->integer('sync_interval_minutes')->default(5); // How often to sync
            $table->json('capabilities')->nullable(); // Device capabilities (features supported)
            $table->json('settings')->nullable(); // Device-specific settings
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('attendance_locations')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('device_id');
            $table->index('device_type');
            $table->index('location_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_devices');
    }
};
