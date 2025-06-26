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
        Schema::create('activity_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable();
            $table->string('room')->nullable();
            $table->enum('recurring', ['weekly', 'biweekly', 'monthly', 'one_time'])->default('weekly');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'cancelled', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->integer('max_capacity')->nullable(); // Override activity max_participants if needed
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['activity_id', 'status']);
            $table->index(['day_of_week', 'start_time']);
            $table->index(['status', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_schedules');
    }
};