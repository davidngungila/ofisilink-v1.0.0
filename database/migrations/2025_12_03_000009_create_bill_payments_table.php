<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no')->unique();
            $table->unsignedBigInteger('bill_id');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['Cash', 'Bank Transfer', 'Cheque', 'Mobile Money', 'Credit Card', 'Other'])->default('Bank Transfer');
            $table->string('reference_no')->nullable(); // Cheque number, transaction reference
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('restrict');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->index(['payment_date', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
    }
};



