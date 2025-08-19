<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for User Management Module.
     * Creates: users, password_resets, sessions tables
     */
    public function up(): void
    {
        // 1. USERS TABLE
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('iium_id', 50)->unique()->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('education_level', 100)->nullable();
            $table->string('education_specialization')->nullable();
            $table->string('teaching_specialization')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('role', ['admin', 'supervisor', 'teacher', 'ajk']);
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->string('centre_id', 10)->nullable();
            $table->string('encrypted_id')->nullable();
            $table->string('avatar')->nullable();
            $table->string('position', 100)->nullable();
            $table->text('about')->nullable();
            $table->string('centre_location')->nullable();
            $table->timestamp('user_last_accessed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes
            $table->index('role');
            $table->index('centre_id');
            $table->index('status');
        });

        // 2. PASSWORD_RESETS TABLE
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. SESSIONS TABLE
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
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
};