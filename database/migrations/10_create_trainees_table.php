<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
            $table->string('trainee_first_name');
            $table->string('trainee_last_name');
            $table->string('trainee_email')->unique();
            $table->string('trainee_phone_number')->nullable();
            $table->date('trainee_date_of_birth')->nullable();
            $table->timestamp('trainee_last_accessed_at')->nullable();
            $table->string('centre_name')->nullable();
            $table->string('trainee_avatar')->nullable();
            
            $table->foreign('centre_name')
                ->references('centre_name')
                ->on('centres')
                ->onDelete('set null');
            
            $table->integer('trainee_attendance')->default(0);
            $table->string('trainee_condition')->nullable();
            $table->string('course_id')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainees');
    }
};