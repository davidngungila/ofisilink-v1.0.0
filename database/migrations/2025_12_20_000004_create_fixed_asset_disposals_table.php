<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained('fixed_assets')->onDelete('restrict');
            $table->date('disposal_date');
            $table->enum('disposal_method', ['Sale', 'Scrap', 'Donation', 'Trade In', 'Write Off', 'Other'])->default('Sale');
            $table->decimal('disposal_proceeds', 15, 2)->default(0);
            $table->decimal('net_book_value_at_disposal', 15, 2);
            $table->decimal('gain_loss', 15, 2); // disposal_proceeds - net_book_value_at_disposal
            $table->string('disposal_reference')->nullable();
            $table->text('disposal_reason')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_posted')->default(false);
            $table->date('posted_date')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamps();
            
            $table->foreign('journal_entry_id', 'fa_dis_je_fk')->references('id')->on('journal_entries')->onDelete('set null');
            $table->foreign('created_by', 'fa_dis_created_by_fk')->references('id')->on('users')->onDelete('set null');
            $table->foreign('posted_by', 'fa_dis_posted_by_fk')->references('id')->on('users')->onDelete('set null');
            
            $table->index('fixed_asset_id');
            $table->index('disposal_date');
            $table->index('is_posted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_disposals');
    }
};

