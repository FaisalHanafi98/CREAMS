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
        // Drop activity_schedules table as it's redundant with activity_sessions
        // activity_sessions table provides more comprehensive session management
        // including detailed scheduling, attendance tracking, and session metadata
        Schema::dropIfExists('activity_schedules');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate basic activity_schedules table structure for rollback
        Schema::create('activity_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('venue')->nullable();
            $table->timestamps();
            
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
        });
    }
};
