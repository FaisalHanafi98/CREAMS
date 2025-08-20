<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for User Management and Centre Management.
     * These are the foundational tables that other tables depend on.
     */
    public function up(): void
    {
        // Skip if tables already exist (for existing installations with schema dump)
        if (Schema::hasTable('centres')) {
            return;
        }

        // 1. CENTRES TABLE - Must be created first as it's referenced by users
        Schema::create('centres', function (Blueprint $table) {
            $table->string('centre_id', 10)->primary();
            $table->string('centre_name')->unique();
            $table->text('centre_address');
            $table->string('centre_phone', 20);
            $table->string('centre_email')->unique();
            $table->string('centre_capacity');
            $table->string('centre_manager');
            $table->string('centre_manager_contact', 20);
            $table->enum('centre_status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->text('centre_description')->nullable();
            $table->json('centre_facilities')->nullable();
            $table->string('centre_image')->nullable();
            $table->decimal('centre_latitude', 10, 8)->nullable();
            $table->decimal('centre_longitude', 11, 8)->nullable();
            $table->time('opening_time')->default('08:00:00');
            $table->boolean('is_active')->default(true);
            $table->json('attendance_policies')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('centre_status');
            $table->index('is_active');
        });

        // 2. USERS TABLE - References centres
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('ic_number', 20)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'supervisor', 'teacher', 'ajk'])->default('teacher');
            $table->string('phone_number', 20)->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->text('qualifications')->nullable();
            $table->text('specializations')->nullable();
            $table->date('date_joined')->nullable();
            $table->enum('employment_status', ['full_time', 'part_time', 'contract', 'volunteer'])->default('full_time');
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('centre_id', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('profile_picture')->nullable();
            $table->text('notes')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes
            $table->index('role');
            $table->index('centre_id');
            $table->index('is_active');
            $table->index('employment_status');
        });

        // 3. PASSWORD RESET TOKENS TABLE
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 4. SESSIONS TABLE
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('centres');
    }
};