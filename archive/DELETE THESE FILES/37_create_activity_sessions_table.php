<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('teacher_id');
            $table->string('class_name');
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('duration_hours', 3, 1);
            $table->string('location');
            $table->integer('max_capacity');
            $table->integer('current_enrollment')->default(0);
            $table->string('semester');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users');
            $table->index(['activity_id', 'class_name', 'semester']);
            $table->index(['teacher_id', 'day_of_week']);
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_sessions');
    }
};