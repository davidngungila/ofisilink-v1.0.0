<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained('fixed_assets')->onDelete('cascade');
            $table->date('maintenance_date');
            $table->enum('maintenance_type', ['Routine', 'Repair', 'Upgrade', 'Inspection', 'Other'])->default('Routine');
            $table->string('service_provider')->nullable();
            $table->text('description');
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('invoice_number')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->enum('status', ['Scheduled', 'In Progress', 'Completed', 'Cancelled'])->default('Scheduled');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by', 'fa_maint_created_by_fk')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'fa_maint_updated_by_fk')->references('id')->on('users')->onDelete('set null');
            
            $table->index('fixed_asset_id');
            $table->index('maintenance_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_maintenances');
    }
};

