<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('depreciation_method', ['Straight Line', 'Declining Balance', 'Units of Production', 'Sum of Years Digits'])->default('Straight Line');
            $table->decimal('default_depreciation_rate', 5, 2)->default(0); // Annual percentage
            $table->integer('default_useful_life_years')->default(5);
            $table->unsignedBigInteger('asset_account_id')->nullable(); // Chart of Account for asset
            $table->unsignedBigInteger('depreciation_expense_account_id')->nullable(); // Chart of Account for depreciation expense
            $table->unsignedBigInteger('accumulated_depreciation_account_id')->nullable(); // Chart of Account for accumulated depreciation
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('asset_account_id', 'fa_cat_asset_acc_fk')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('depreciation_expense_account_id', 'fa_cat_dep_exp_acc_fk')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('accumulated_depreciation_account_id', 'fa_cat_acc_dep_acc_fk')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('created_by', 'fa_cat_created_by_fk')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'fa_cat_updated_by_fk')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_categories');
    }
};

