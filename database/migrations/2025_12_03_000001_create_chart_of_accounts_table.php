<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['Asset', 'Liability', 'Equity', 'Income', 'Expense'])->index();
            $table->enum('category', [
                // Assets
                'Current Asset', 'Fixed Asset', 'Non-Current Asset',
                // Liabilities
                'Current Liability', 'Non-Current Liability',
                // Equity
                'Equity', 'Retained Earnings',
                // Income
                'Operating Income', 'Non-Operating Income',
                // Expenses
                'Operating Expense', 'Non-Operating Expense', 'Cost of Goods Sold'
            ])->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->date('opening_balance_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System accounts cannot be deleted
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};



