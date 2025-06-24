<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('teacher_id');
            $table->string('session_code')->unique();
            
            // Schedule
            $table->datetime('scheduled_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes');
            
            // Location
            $table->string('venue')->nullable();
            $table->string('room_number')->nullable();
            
            // Capacity
            $table->integer('max_participants');
            $table->integer('enrolled_count')->default(0);
            
            // Status
            $table->enum('status', [
                'scheduled',
                'ongoing',
                'completed',
                'cancelled',
                'postponed'
            ])->default('scheduled');
            
            // Additional info
            $table->text('notes')->nullable();
            $table->text('materials_prepared')->nullable();
            $table->boolean('attendance_marked')->default(false);
            
            // Completion details
            $table->datetime('actual_start')->nullable();
            $table->datetime('actual_end')->nullable();
            $table->text('session_report')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['activity_id', 'scheduled_date']);
            $table->index(['teacher_id', 'scheduled_date']);
            $table->index(['status', 'scheduled_date']);
            
            // Foreign keys
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_sessions');
    }
};