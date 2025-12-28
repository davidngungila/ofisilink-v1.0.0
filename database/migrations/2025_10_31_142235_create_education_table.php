<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('education', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('level'); // primary, secondary, diploma, degree, masters, phd
            $table->string('institution');
            $table->string('field_of_study')->nullable();
            $table->string('qualification')->nullable(); // Certificate, Diploma, Bachelor, Master, PhD
            $table->year('start_year')->nullable();
            $table->year('end_year')->nullable();
            $table->string('grade')->nullable();
            $table->text('notes')->nullable();
            $table->integer('order')->default(0); // For ordering multiple records
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
