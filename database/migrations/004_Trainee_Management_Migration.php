<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Trainee Management Module.
     * Creates: trainees, trainee_attendances tables
     */
    public function up(): void
    {
        // 1. TRAINEES TABLE
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
            $table->string('trainee_id', 50)->unique();
            $table->string('trainee_first_name', 100);
            $table->string('trainee_last_name', 100);
            $table->string('trainee_email')->unique();
            $table->string('ic_number', 15)->unique();
            $table->date('trainee_date_of_birth');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('trainee_phone_number', 20)->nullable();
            $table->text('trainee_address')->nullable();
            $table->string('trainee_condition')->nullable();
            $table->string('centre_id', 10)->nullable();
            $table->string('centre_name')->nullable();
            $table->foreignId('course_id')->nullable();
            $table->enum('status', ['active', 'inactive', 'graduated'])->default('active');
            
            // Guardian Information
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_relationship', 50)->nullable();
            $table->text('guardian_address')->nullable();
            
            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relationship', 50)->nullable();
            
            // Consent and Additional Info
            $table->boolean('photo_consent')->default(false);
            $table->boolean('services_consent')->default(false);
            $table->text('medical_history')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('avatar')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('centre_id');
            $table->index('status');
            $table->index('trainee_condition');
            $table->index('gender');
        });

        // 2. TRAINEE_ATTENDANCES TABLE
        Schema::create('trainee_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('marked_by_user_id')->nullable();
            $table->timestamp('marked_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('trainee_id');
            $table->index('activity_id');
            $table->index('session_id');
            $table->index('attendance_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainee_attendances');
        Schema::dropIfExists('trainees');
    }
};