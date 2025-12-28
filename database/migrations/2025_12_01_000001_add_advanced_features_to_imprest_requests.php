<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imprest_requests', function (Blueprint $table) {
            // Advanced fields
            $table->string('currency', 3)->default('TZS')->after('amount');
            $table->decimal('exchange_rate', 10, 4)->default(1)->after('currency');
            $table->decimal('approved_amount', 15, 2)->nullable()->after('amount');
            $table->decimal('final_amount', 15, 2)->nullable()->after('approved_amount');
            $table->unsignedBigInteger('department_id')->nullable()->after('accountant_id');
            $table->unsignedBigInteger('budget_category_id')->nullable()->after('department_id');
            $table->boolean('is_recurring')->default(false)->after('priority');
            $table->unsignedBigInteger('recurring_template_id')->nullable()->after('is_recurring');
            $table->string('recurring_frequency')->nullable()->after('recurring_template_id'); // daily, weekly, monthly, quarterly, yearly
            $table->date('recurring_end_date')->nullable()->after('recurring_frequency');
            $table->enum('approval_type', ['full', 'partial'])->default('full')->after('status');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('rejection_reason');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('internal_notes')->nullable()->after('description');
            $table->json('custom_fields')->nullable()->after('internal_notes');
            
            // Foreign keys
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('imprest_requests', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'currency', 'exchange_rate', 'approved_amount', 'final_amount',
                'department_id', 'budget_category_id', 'is_recurring', 'recurring_template_id',
                'recurring_frequency', 'recurring_end_date', 'approval_type',
                'rejection_reason', 'rejected_by', 'rejected_at',
                'internal_notes', 'custom_fields'
            ]);
        });
    }
};






