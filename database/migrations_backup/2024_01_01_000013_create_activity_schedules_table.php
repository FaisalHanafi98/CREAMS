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
            $table->string('schedule_name');
            $table->text('schedule_description')->nullable();
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly', 'yearly', 'custom'])->default('weekly');
            $table->json('recurrence_pattern')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location');
            $table->enum('schedule_status', ['active', 'inactive', 'paused'])->default('active');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->index(['activity_id']);
            $table->index(['schedule_status']);
            $table->index(['start_date']);
            $table->index(['end_date']);
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