<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // Technician/HR
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['reported', 'in_progress', 'resolved', 'closed', 'cancelled'])->default('reported');
            $table->string('issue_type'); // 'maintenance', 'damage', 'loss', 'theft', 'other'
            $table->string('title');
            $table->text('description');
            $table->text('resolution_notes')->nullable();
            $table->date('reported_date')->default(now());
            $table->date('resolved_date')->nullable();
            $table->decimal('cost', 15, 2)->nullable(); // Cost of resolution
            $table->timestamps();
            
            $table->index('asset_id');
            $table->index('status');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_issues');
    }
};

