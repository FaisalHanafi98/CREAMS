<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('enrolled_by');
            $table->datetime('enrollment_date');
            $table->enum('status', ['active', 'withdrawn', 'completed'])->default('active');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('enrolled_by')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->unique(['session_id', 'trainee_id']);
            $table->index(['trainee_id', 'status']);
            $table->index('enrollment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_enrollments');
    }
};