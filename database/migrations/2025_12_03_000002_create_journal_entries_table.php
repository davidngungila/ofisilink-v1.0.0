<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_no')->unique();
            $table->date('entry_date');
            $table->string('reference_no')->nullable();
            $table->text('description');
            $table->enum('status', ['Draft', 'Posted', 'Reversed', 'Cancelled'])->default('Draft')->index();
            $table->enum('source', [
                'Manual', 'Sales', 'Purchase', 'Payroll', 'Petty Cash', 
                'Imprest', 'Bank', 'Asset', 'Inventory', 'Adjustment'
            ])->default('Manual');
            $table->string('source_ref')->nullable(); // Reference to source document
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('posted_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['entry_date', 'status']);
            $table->index(['source', 'source_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};



