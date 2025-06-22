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
        Schema::table('trainees', function (Blueprint $table) {
            // Medical Information
            $table->string('medical_condition')->nullable()->after('trainee_condition');
            $table->text('medical_history')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('doctor_contact')->nullable();
            $table->text('special_requirements')->nullable();
            
            // Guardian Information
            $table->string('guardian_name')->nullable();
            $table->enum('guardian_relationship', ['parent', 'sibling', 'grandparent', 'legal_guardian', 'other'])->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->text('guardian_address')->nullable();
            
            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            
            // Additional Information
            $table->json('preferred_activities')->nullable(); // Store as JSON array
            $table->text('additional_notes')->nullable();
            $table->string('referral_source')->nullable();
            $table->boolean('data_consent')->default(false);
            
            // Registration metadata
            $table->timestamp('registration_completed_at')->nullable();
            $table->enum('registration_status', ['incomplete', 'pending_review', 'approved', 'rejected'])->default('incomplete');
            
            // Enhanced photo handling
            $table->string('photo_path')->nullable()->after('trainee_avatar');
            
            // Add indexes for better performance
            $table->index(['registration_status']);
            $table->index(['medical_condition']);
            $table->index(['guardian_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            $table->dropColumn([
                'medical_condition',
                'medical_history',
                'doctor_name',
                'doctor_contact',
                'special_requirements',
                'guardian_name',
                'guardian_relationship',
                'guardian_email',
                'guardian_phone',
                'guardian_address',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
                'preferred_activities',
                'additional_notes',
                'referral_source',
                'data_consent',
                'registration_completed_at',
                'registration_status',
                'photo_path'
            ]);
        });
    }
};