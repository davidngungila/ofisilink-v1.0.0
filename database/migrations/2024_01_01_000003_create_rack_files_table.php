<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rack_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('rack_folders');
            $table->string('file_name');
            $table->string('file_number')->unique();
            $table->text('description')->nullable();
            $table->enum('file_type', ['general', 'contract', 'financial', 'legal', 'hr', 'technical'])->default('general');
            $table->enum('confidential_level', ['normal', 'confidential', 'strictly_confidential'])->default('normal');
            $table->string('tags')->nullable();
            $table->date('file_date')->default(now());
            $table->integer('retention_period')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', ['available', 'issued', 'archived'])->default('available');
            $table->foreignId('current_holder')->nullable()->constrained('users');
            $table->timestamp('last_returned')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rack_files');
    }
};








