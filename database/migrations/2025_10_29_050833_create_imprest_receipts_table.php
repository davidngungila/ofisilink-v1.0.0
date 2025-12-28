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
        Schema::create('imprest_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->decimal('receipt_amount', 15, 2);
            $table->string('receipt_description');
            $table->string('receipt_file_path');
            $table->unsignedBigInteger('submitted_by');
            $table->timestamp('submitted_at');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('assignment_id')->references('id')->on('imprest_assignments')->onDelete('cascade');
            $table->foreign('submitted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imprest_receipts');
    }
};
