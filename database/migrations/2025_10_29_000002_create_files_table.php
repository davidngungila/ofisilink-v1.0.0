<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('file_folders')->onDelete('cascade');
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->string('mime_type');
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->enum('access_level', ['public', 'department', 'private'])->default('private');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->string('assigned_users')->nullable();
            $table->string('tags')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->date('expiry_date')->nullable();
            $table->enum('confidential_level', ['normal', 'confidential', 'strictly_confidential'])->default('normal');
            $table->integer('download_count')->default(0);
            $table->timestamps();
            $table->index(['folder_id', 'access_level']);
            $table->index(['uploaded_by', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('files');
    }
};

