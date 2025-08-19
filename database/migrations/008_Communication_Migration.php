<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Communication Module.
     * Creates: contact_messages, messages, notifications, volunteers tables
     */
    public function up(): void
    {
        // 1. CONTACT_MESSAGES TABLE
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['new', 'read', 'replied', 'closed'])->default('new');
            $table->foreignId('replied_by')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->text('reply_message')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('replied_by');
        });

        // 2. MESSAGES TABLE
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('content');
            $table->foreignId('sender_id');
            $table->enum('sender_type', ['user', 'system']);
            $table->enum('message_type', ['general', 'announcement', 'alert', 'reminder'])->default('general');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_draft')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('sender_id');
            $table->index('sender_type');
            $table->index('message_type');
            $table->index('priority');
            $table->index('is_draft');
        });

        // 3. NOTIFICATIONS TABLE
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 4. VOLUNTEERS TABLE
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('occupation')->nullable();
            $table->text('skills')->nullable();
            $table->text('availability')->nullable();
            $table->text('motivation')->nullable();
            $table->enum('status', ['applied', 'reviewed', 'approved', 'rejected', 'active', 'inactive'])->default('applied');
            $table->foreignId('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteers');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('contact_messages');
    }
};