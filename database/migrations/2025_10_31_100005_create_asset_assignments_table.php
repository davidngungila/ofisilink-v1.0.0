<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('asset_assignments')) {
            Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('restrict');
            $table->date('assigned_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['active', 'returned', 'lost'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('returned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('asset_id');
            $table->index('assigned_to');
            $table->index('status');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};

