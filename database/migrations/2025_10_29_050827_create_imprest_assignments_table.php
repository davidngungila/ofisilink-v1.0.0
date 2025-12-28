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
        Schema::create('imprest_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('imprest_request_id');
            $table->unsignedBigInteger('staff_id');
            $table->decimal('assigned_amount', 15, 2);
            $table->text('assignment_notes')->nullable();
            $table->boolean('receipt_submitted')->default(false);
            $table->timestamp('receipt_submitted_at')->nullable();
            $table->unsignedBigInteger('assigned_by');
            $table->timestamp('assigned_at');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('imprest_request_id')->references('id')->on('imprest_requests')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('users');
            $table->foreign('assigned_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imprest_assignments');
    }
};
