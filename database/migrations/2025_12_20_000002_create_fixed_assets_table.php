<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('fixed_asset_categories')->onDelete('restrict');
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('location')->nullable(); // Physical location
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            // Purchase Information
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2);
            $table->decimal('additional_costs', 15, 2)->default(0); // Shipping, installation, etc.
            $table->decimal('total_cost', 15, 2); // purchase_cost + additional_costs
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('set null');
            $table->string('invoice_number')->nullable();
            $table->string('purchase_order_number')->nullable();
            
            // Depreciation Information
            $table->enum('depreciation_method', ['Straight Line', 'Declining Balance', 'Units of Production', 'Sum of Years Digits'])->default('Straight Line');
            $table->decimal('depreciation_rate', 5, 2)->default(0); // Annual percentage
            $table->integer('useful_life_years')->default(5);
            $table->integer('useful_life_units')->nullable(); // For units of production method
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->date('depreciation_start_date');
            $table->date('depreciation_end_date')->nullable(); // Calculated based on useful life
            
            // Current Values
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('net_book_value', 15, 2); // total_cost - accumulated_depreciation
            $table->decimal('current_market_value', 15, 2)->nullable();
            
            // Accounting Integration
            $table->unsignedBigInteger('asset_account_id')->nullable(); // Chart of Account
            $table->unsignedBigInteger('depreciation_expense_account_id')->nullable();
            $table->unsignedBigInteger('accumulated_depreciation_account_id')->nullable();
            
            // Status
            $table->enum('status', ['Active', 'Depreciated', 'Disposed', 'Under Maintenance', 'Written Off'])->default('Active');
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_proceeds', 15, 2)->nullable();
            $table->text('disposal_notes')->nullable();
            
            // Additional Information
            $table->string('warranty_period')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable(); // For additional custom fields
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('asset_account_id', 'fa_asset_acc_fk')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('depreciation_expense_account_id', 'fa_dep_exp_acc_fk')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('accumulated_depreciation_account_id', 'fa_acc_dep_acc_fk')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('created_by', 'fa_created_by_fk')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'fa_updated_by_fk')->references('id')->on('users')->onDelete('set null');
            
            $table->index('asset_code');
            $table->index('status');
            $table->index('purchase_date');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};

