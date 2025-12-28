<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_performance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('review_period'); // e.g., "2024 Q1", "2024 Annual"
            $table->date('review_date');
            $table->enum('review_type', ['Quarterly', 'Annual', 'Probation', 'Promotion', 'Other'])->default('Annual');
            $table->decimal('overall_rating', 3, 2)->nullable(); // 0.00 to 5.00
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals')->nullable();
            $table->text('achievements')->nullable();
            $table->text('comments')->nullable();
            $table->string('recommendation')->nullable(); // Promotion, Training, etc.
            $table->boolean('is_confirmed')->default(false);
            $table->date('next_review_date')->nullable();
            $table->json('performance_metrics')->nullable(); // Store detailed metrics as JSON
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id', 'review_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_performance');
    }
};
