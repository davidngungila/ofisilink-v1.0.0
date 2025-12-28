<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incident_inbox', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable()->index();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->enum('status', ['Pending','Promoted','Ignored'])->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_inbox');
    }
};








