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
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
            $table->string('trainee_id')->unique();
            $table->string('trainee_first_name');
            $table->string('trainee_last_name');
            $table->string('trainee_email')->unique();
            $table->string('ic_number')->unique();
            $table->date('trainee_date_of_birth');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->string('trainee_phone_number')->nullable();
            $table->text('trainee_address')->nullable();
            $table->string('avatar')->nullable();
            $table->string('trainee_condition')->nullable();
            $table->string('centre_name');
            $table->text('medical_history')->nullable();
            $table->text('additional_notes')->nullable();
            $table->boolean('photo_consent')->default(false);
            $table->boolean('services_consent')->default(false);
            $table->enum('status', ['active', 'inactive', 'suspended', 'graduated'])->default('active');
            $table->string('centre_id');
            $table->unsignedBigInteger('course_id')->nullable();
            
            // Guardian information
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_relationship')->nullable();
            $table->text('guardian_address')->nullable();
            
            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            
            $table->timestamps();
            
            $table->index(['trainee_id']);
            $table->index(['centre_id']);
            $table->index(['status']);
            $table->index(['ic_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainees');
    }
};