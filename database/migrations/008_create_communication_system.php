<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Communication System Module
     * Module: Communication (Messages & Notifications)
     * Priority: 008 - Medium (Internal Communication)
     */
    public function up(): void
    {
        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['info', 'success', 'warning', 'error', 'reminder'])->default('info');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable(); // Additional notification data
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('centre_id')->nullable();
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['is_read']);
            $table->index(['type']);
            $table->index(['priority']);
            $table->index(['centre_id']);
        });

        // Internal messages system
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('recipient_id');
            $table->string('subject');
            $table->text('body');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->boolean('sender_deleted')->default(false);
            $table->boolean('recipient_deleted')->default(false);
            $table->json('attachments')->nullable();
            $table->unsignedBigInteger('reply_to')->nullable(); // For message threading
            $table->string('message_thread_id')->nullable(); // Group related messages
            $table->string('centre_id')->nullable();
            $table->timestamps();
            
            $table->index(['sender_id']);
            $table->index(['recipient_id']);
            $table->index(['is_read']);
            $table->index(['priority']);
            $table->index(['message_thread_id']);
            $table->index(['centre_id']);
        });

        // Contact messages from public forms
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->enum('inquiry_type', ['general', 'enrollment', 'volunteer', 'feedback', 'support'])->default('general');
            $table->enum('status', ['new', 'in_progress', 'resolved', 'closed'])->default('new');
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('replied_by')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('centre_id')->nullable(); // If inquiry is centre-specific
            $table->timestamps();
            
            $table->index(['status']);
            $table->index(['inquiry_type']);
            $table->index(['email']);
            $table->index(['centre_id']);
        });

        // Volunteer applications
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('volunteer_name');
            $table->string('volunteer_email')->unique();
            $table->string('volunteer_phone');
            $table->text('volunteer_address');
            $table->date('volunteer_birth_date');
            $table->enum('volunteer_gender', ['Male', 'Female', 'Other']);
            $table->text('volunteer_skills')->nullable();
            $table->text('volunteer_experience')->nullable();
            $table->string('volunteer_availability')->nullable(); // Days/times available
            $table->enum('volunteer_status', ['pending', 'active', 'inactive'])->default('pending');
            $table->date('volunteer_start_date')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('centre_id')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamps();
            
            $table->index(['volunteer_status']);
            $table->index(['volunteer_email']);
            $table->index(['centre_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteers');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('notifications');
    }
};