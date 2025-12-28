<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->enum('maintenance_type', ['preventive', 'corrective', 'inspection', 'upgrade'])->default('preventive');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('vendor_id')->nullable(); // External vendor if applicable
            $table->string('vendor_name')->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('asset_id');
            $table->index('status');
            $table->index('scheduled_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance');
    }
};

