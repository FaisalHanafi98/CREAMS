<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('session_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('trainee_id');
            $table->boolean('attended')->default(false);
            $table->unsignedBigInteger('recorded_by'); // Staff who marked attendance
            $table->timestamp('recorded_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['session_id', 'trainee_id']);
            $table->index(['trainee_id']);
            $table->index(['recorded_at']);
            
            // Unique constraint to prevent duplicate attendance records
            $table->unique(['session_id', 'trainee_id'], 'session_trainee_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_attendance');
    }
};
