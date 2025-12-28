<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('main_tasks', function (Blueprint $table) {
            $table->enum('priority', ['Low', 'Normal', 'High', 'Critical'])->default('Normal')->after('status');
            $table->string('category')->nullable()->after('priority');
            $table->text('tags')->nullable()->after('category');
            $table->integer('progress_percentage')->default(0)->after('tags');
            $table->decimal('budget', 15, 2)->nullable()->after('progress_percentage');
            $table->decimal('actual_cost', 15, 2)->nullable()->after('budget');
        });
        
        Schema::table('task_activities', function (Blueprint $table) {
            $table->enum('priority', ['Low', 'Normal', 'High', 'Critical'])->default('Normal')->after('status');
            $table->integer('estimated_hours')->nullable()->after('priority');
            $table->integer('actual_hours')->nullable()->after('estimated_hours');
            $table->unsignedBigInteger('depends_on_id')->nullable()->after('actual_hours');
            
            $table->foreign('depends_on_id')->references('id')->on('task_activities')->onDelete('set null');
        });
        
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('main_task_id')->nullable();
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->text('comment');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
            
            $table->foreign('main_task_id')->references('id')->on('main_tasks')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('task_activities')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('main_task_id')->nullable();
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
            
            $table->foreign('main_task_id')->references('id')->on('main_tasks')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('task_activities')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_comments');
        
        Schema::table('task_activities', function (Blueprint $table) {
            $table->dropForeign(['depends_on_id']);
            $table->dropColumn(['priority', 'estimated_hours', 'actual_hours', 'depends_on_id']);
        });
        
        Schema::table('main_tasks', function (Blueprint $table) {
            $table->dropColumn(['priority', 'category', 'tags', 'progress_percentage', 'budget', 'actual_cost']);
        });
    }
};







