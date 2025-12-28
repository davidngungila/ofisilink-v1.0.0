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
        Schema::create('leave_dependents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_request_id');
            $table->string('name');
            $table->string('relationship');
            $table->string('certificate_path')->nullable();
            $table->decimal('fare_amount', 10, 2)->default(0);
            $table->timestamps();
            
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_dependents');
    }
};