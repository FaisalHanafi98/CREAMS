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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('iium_id')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('position')->nullable();
            $table->string('education_level')->nullable();
            $table->string('education_specialization')->nullable();
            $table->string('teaching_specialization')->nullable();
            $table->string('avatar')->nullable();
            $table->text('about')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('role', ['admin', 'supervisor', 'teacher', 'ajk'])->default('teacher');
            $table->enum('status', ['active', 'inactive', 'pending', 'suspended'])->default('pending');
            $table->string('centre_id')->nullable();
            $table->string('centre_location')->nullable();
            $table->timestamp('user_last_accessed_at')->nullable();
            $table->text('review')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['role', 'status']);
            $table->index(['centre_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};