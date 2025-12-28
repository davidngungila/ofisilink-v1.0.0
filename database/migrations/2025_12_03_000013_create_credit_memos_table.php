<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_memos', function (Blueprint $table) {
            $table->id();
            $table->string('memo_no')->unique();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->date('memo_date');
            $table->enum('type', ['Return', 'Discount', 'Adjustment', 'Write-off'])->default('Return');
            $table->decimal('amount', 15, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['Draft', 'Posted', 'Cancelled'])->default('Draft');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->index(['memo_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_memos');
    }
};



