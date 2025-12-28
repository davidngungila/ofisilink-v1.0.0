<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rack_file_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('rack_files');
            $table->foreignId('requested_by')->constrained('users');
            $table->text('purpose');
            $table->date('expected_return_date')->nullable();
            $table->enum('urgency', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('manager_notes')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rack_file_requests');
    }
};








