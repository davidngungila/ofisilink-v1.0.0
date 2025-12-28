<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rack_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('rack_folders');
            $table->foreignId('user_id')->constrained('users');
            $table->string('activity_type');
            $table->timestamp('activity_date')->useCurrent();
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rack_activities');
    }
};








