<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained('fixed_assets')->onDelete('cascade');
            $table->date('depreciation_date');
            $table->string('period'); // e.g., "2025-01", "2025-Q1", "2025"
            $table->enum('period_type', ['Monthly', 'Quarterly', 'Yearly'])->default('Monthly');
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('accumulated_depreciation_before', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation_after', 15, 2);
            $table->decimal('net_book_value_before', 15, 2);
            $table->decimal('net_book_value_after', 15, 2);
            $table->text('calculation_details')->nullable(); // JSON or text with calculation breakdown
            $table->boolean('is_posted')->default(false);
            $table->date('posted_date')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable(); // Link to journal entry if posted
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamps();
            
            $table->foreign('journal_entry_id', 'fa_dep_je_fk')->references('id')->on('journal_entries')->onDelete('set null');
            $table->foreign('created_by', 'fa_dep_created_by_fk')->references('id')->on('users')->onDelete('set null');
            $table->foreign('posted_by', 'fa_dep_posted_by_fk')->references('id')->on('users')->onDelete('set null');
            
            $table->index('fixed_asset_id');
            $table->index('depreciation_date');
            $table->index('period');
            $table->index('is_posted');
            $table->unique(['fixed_asset_id', 'period', 'period_type'], 'fa_dep_unique_period'); // Prevent duplicate depreciation for same period
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_depreciations');
    }
};

