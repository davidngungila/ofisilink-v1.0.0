<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('file_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->nullable()->constrained('files')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('activity_type');
            $table->timestamp('activity_date')->useCurrent();
            $table->json('details')->nullable();
            $table->timestamps();
            
            $table->index(['file_id', 'activity_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('file_activities');
    }
};

