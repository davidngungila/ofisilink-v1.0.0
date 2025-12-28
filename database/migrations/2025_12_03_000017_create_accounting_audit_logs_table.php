<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module'); // Chart of Accounts, Journal, Invoice, Bill, etc.
            $table->string('action'); // Create, Update, Delete, Post, Approve, Reverse
            $table->string('record_type'); // Model name
            $table->unsignedBigInteger('record_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->index(['module', 'record_type', 'record_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_audit_logs');
    }
};



