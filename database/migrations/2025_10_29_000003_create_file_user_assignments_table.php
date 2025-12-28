<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('file_user_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users');
            $table->enum('permission_level', ['view', 'edit', 'manage'])->default('view');
            $table->date('expiry_date')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate assignments
            $table->unique(['file_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('file_user_assignments');
    }
};

