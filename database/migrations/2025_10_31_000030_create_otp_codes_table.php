<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('otp_codes')) {
            Schema::create('otp_codes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('otp_code', 6);
                $table->timestamp('expires_at');
                $table->boolean('used')->default(false);
                $table->timestamp('used_at')->nullable();
                $table->string('purpose')->default('login');
                $table->string('ip_address')->nullable();
                $table->timestamps();
                $table->index(['user_id','purpose','used']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};








