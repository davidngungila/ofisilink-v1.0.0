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
        Schema::create('petty_cash_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->date('date');
            $table->string('payee');
            $table->text('purpose');
            $table->decimal('amount', 15, 2);
            $table->enum('status', [
                'pending_accountant',
                'pending_hod', 
                'pending_ceo',
                'approved_for_payment',
                'paid',
                'rejected',
                'retired',
                'pending_retirement_review',
                'pending_retirement_hod',
                'pending_retirement_ceo'
            ])->default('pending_accountant');
            
            // GL Account assignments (Accountant)
            $table->unsignedBigInteger('gl_account_id')->nullable();
            $table->unsignedBigInteger('cash_box_id')->nullable();
            
            // Approval tracking
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('accountant_id')->nullable();
            $table->unsignedBigInteger('hod_id')->nullable();
            $table->unsignedBigInteger('ceo_id')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            
            // Timestamps for each stage
            $table->timestamp('accountant_verified_at')->nullable();
            $table->timestamp('hod_approved_at')->nullable();
            $table->timestamp('ceo_approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('retired_at')->nullable();
            
            // Comments and attachments
            $table->text('accountant_comments')->nullable();
            $table->text('hod_comments')->nullable();
            $table->text('ceo_comments')->nullable();
            $table->text('retirement_comments')->nullable();
            $table->json('attachments')->nullable();
            $table->json('retirement_receipts')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('accountant_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('hod_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('ceo_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_vouchers');
    }
};
