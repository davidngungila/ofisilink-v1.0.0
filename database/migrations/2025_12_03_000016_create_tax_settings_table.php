<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tax_name'); // VAT, GST, Withholding Tax, PAYE
            $table->string('tax_code')->unique();
            $table->decimal('rate', 5, 2); // Percentage
            $table->enum('tax_type', ['VAT', 'GST', 'Withholding Tax', 'PAYE', 'Corporate Tax', 'Other'])->default('VAT');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('account_id')->nullable(); // Tax liability account
            $table->timestamps();
            
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->index(['tax_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_settings');
    }
};



