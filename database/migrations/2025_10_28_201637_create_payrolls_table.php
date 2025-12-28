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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('pay_period'); // Format: YYYY-MM
            $table->date('pay_date');
            $table->enum('status', ['processed', 'reviewed', 'approved', 'paid', 'rejected', 'cancelled'])->default('processed');
            $table->unsignedBigInteger('processed_by');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('approval_notes')->nullable();
            $table->enum('payment_method', ['bank_transfer', 'cash', 'cheque'])->nullable();
            $table->date('payment_date')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->timestamps();

            $table->foreign('processed_by')->references('id')->on('users');
            $table->foreign('reviewed_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('paid_by')->references('id')->on('users');
            
            $table->index(['pay_period', 'status']);
            $table->unique(['pay_period', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};