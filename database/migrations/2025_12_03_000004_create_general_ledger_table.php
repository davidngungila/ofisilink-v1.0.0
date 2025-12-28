<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_ledger', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->date('transaction_date');
            $table->string('reference_type')->nullable(); // JournalEntry, Invoice, Bill, Payment, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->enum('type', ['Debit', 'Credit']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance', 15, 2)->default(0); // Running balance
            $table->text('description')->nullable();
            $table->string('source')->nullable(); // Manual, Sales, Purchase, etc.
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['account_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['transaction_date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_ledger');
    }
};



