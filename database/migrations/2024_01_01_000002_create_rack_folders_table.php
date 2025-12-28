<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rack_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('rack_number');
            $table->integer('rack_range_start');
            $table->integer('rack_range_end');
            $table->foreignId('category_id')->constrained('rack_categories');
            $table->enum('access_level', ['public', 'department', 'private'])->default('public');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rack_folders');
    }
};








