<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreamsClientManagementTables extends Migration
{
    /**
     * CREAMS Client Management Migration
     * Tables: trainees, volunteers, contact_messages
     * Dependencies: Foundation Management
     */
    public function up(): void
    {
        // Skip if tables already exist (preserves current logic)
        if (Schema::hasTable('trainees')) {
            return;
        }

        // 1. TRAINEES - Service recipients (preserves current structure)
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
            $table->string('trainee_id', 50)->unique(); // Format: PY0001 (disability_code + 4 digits)
            $table->string('trainee_first_name', 100);
            $table->string('trainee_last_name', 100);
            $table->string('trainee_email')->unique(); // Valid domains: gmail, outlook, yahoo, hotmail etc.
            $table->string('ic_number', 15)->unique();
            $table->date('trainee_date_of_birth');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('trainee_phone_number', 20)->nullable();
            $table->text('trainee_address')->nullable();
            $table->string('trainee_condition')->nullable();
            $table->string('centre_id', 10)->nullable();
            $table->string('centre_name')->nullable();
            // Removed course_id - old implementation, replaced by activity system
            $table->enum('status', ['active', 'inactive', 'graduated'])->default('active');
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_relationship', 50)->nullable(); // Must be in English
            $table->text('guardian_address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relationship', 50)->nullable(); // Must be in English
            // Three consent types (all mandatory - value 1)
            $table->boolean('photo_consent')->default(false);
            $table->boolean('services_consent')->default(false);
            $table->boolean('data_consent')->default(false); // Third consent field
            $table->date('registration_date')->default(now());
            $table->timestamps();
            
            $table->index(['centre_id', 'status']);
            $table->index('ic_number');
        });

        // 2. VOLUNTEERS - Community supporters (preserves current structure)
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('volunteer_id', 50)->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('occupation')->nullable();
            $table->string('skills')->nullable();
            $table->text('availability')->nullable();
            $table->string('centre_id', 10)->nullable();
            $table->enum('status', ['applied', 'reviewed', 'approved', 'rejected', 'active', 'inactive'])->default('applied');
            $table->text('motivation')->nullable();
            $table->date('registration_date')->default(now());
            $table->timestamps();
            
            $table->index(['centre_id', 'status']);
        });

        // 3. CONTACT MESSAGES - Public inquiries (preserves current structure)
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('subject');
            $table->text('message');
            $table->enum('inquiry_type', ['general', 'services', 'volunteer', 'donation', 'other'])->default('general');
            $table->enum('status', ['new', 'read', 'replied', 'resolved'])->default('new');
            $table->string('centre_id', 10)->nullable();
            $table->unsignedBigInteger('replied_by')->nullable(); // matches staffs.id (bigint unsigned)
            $table->timestamp('replied_at')->nullable();
            $table->text('reply_message')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'inquiry_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('volunteers');
        Schema::dropIfExists('trainees');
    }
}