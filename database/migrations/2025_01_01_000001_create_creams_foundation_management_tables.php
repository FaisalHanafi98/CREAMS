<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreamsFoundationManagementTables extends Migration
{
    /**
     * CREAMS Foundation Management Migration
     * Tables: centres, users, failed_jobs, sessions
     * Note: personal_access_tokens handled by Laravel Sanctum migration
     * Dependencies: None (runs first)
     */
    public function up(): void
    {
        // Skip if tables already exist (preserves current logic)
        if (Schema::hasTable('centres')) {
            return;
        }

        // 1. CENTRES TABLE - Multi-tenant foundation (preserves current structure)
        Schema::create('centres', function (Blueprint $table) {
            $table->string('centre_id', 10)->primary();
            $table->string('centre_name')->unique();
            $table->text('centre_address')->nullable();
            $table->string('centre_phone', 20)->nullable();
            $table->string('centre_email')->unique();
            $table->string('centre_capacity', 10)->nullable();
            $table->string('centre_manager')->nullable();
            $table->string('centre_manager_contact', 20)->nullable();
            $table->enum('centre_status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->text('centre_description')->nullable();
            $table->json('centre_facilities')->nullable();
            $table->time('opening_time')->default('08:00:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes (preserves current performance patterns)
            $table->index('centre_status');
            $table->index('is_active');
        });

        // 2. USERS TABLE - Staff management (preserves current structure)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('iium_id', 50)->nullable()->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('education_level', 100)->nullable();
            $table->string('education_specialization')->nullable();
            $table->string('teaching_specialization')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('role', ['admin', 'supervisor', 'teacher', 'ajk'])->default('teacher');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->string('centre_id', 10)->nullable();
            $table->string('encrypted_id')->nullable();
            $table->string('avatar')->nullable();
            $table->string('position', 100)->nullable();
            $table->text('about')->nullable();
            $table->string('centre_location')->nullable();
            // Removed bio field - old implementation, about field is sufficient
            $table->timestamp('user_last_accessed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes (preserves current performance patterns)
            $table->index(['centre_id', 'role', 'status']);
        });

        // 3. SYSTEM TABLES (preserves current Laravel patterns)
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

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
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('users');
        Schema::dropIfExists('centres');
    }
}