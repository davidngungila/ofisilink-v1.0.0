<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('file_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('folder_code')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('file_folders')->onDelete('cascade');
            $table->string('path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('access_level', ['public', 'department', 'private'])->default('private');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('file_folders');
    }
};

