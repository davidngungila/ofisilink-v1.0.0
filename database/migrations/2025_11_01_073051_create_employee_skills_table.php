<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_skills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('skill_name');
            $table->enum('skill_category', ['Technical', 'Soft', 'Language', 'Certification', 'Other'])->default('Technical');
            $table->enum('proficiency_level', ['Beginner', 'Intermediate', 'Advanced', 'Expert'])->default('Intermediate');
            $table->integer('years_of_experience')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_certified')->default(false);
            $table->date('certification_date')->nullable();
            $table->string('certification_authority')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'skill_category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_skills');
    }
};
