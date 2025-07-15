<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('trainee_id');
            $table->date('attendance_date');
            $table->enum('status', ['Present', 'Absent', 'Excused', 'Late']);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('marked_by');
            $table->timestamps();
            
            $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees');
            $table->foreign('marked_by')->references('id')->on('users');
            $table->unique(['session_id', 'trainee_id', 'attendance_date']);
            $table->index(['session_id', 'attendance_date']);
            $table->index('trainee_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_attendance');
    }
};