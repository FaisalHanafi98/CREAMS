<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreamsCommunicationManagementTables extends Migration
{
    /**
     * CREAMS Communication Management Migration
     * Tables: messages, message_recipients, notifications, letters, letter_templates
     * Dependencies: Foundation Management
     */
    public function up(): void
    {
        // Skip if tables already exist (preserves current logic)
        if (Schema::hasTable('messages')) {
            return;
        }

        // 1. MESSAGES - Internal messaging (preserves current structure)
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->integer('sender_id');
            $table->string('subject');
            $table->text('message_body');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['draft', 'sent', 'read', 'archived'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
            
            $table->index(['sender_id', 'status', 'sent_at']);
            $table->index('priority');
        });

        // 2. MESSAGE RECIPIENTS - Message delivery (preserves current structure)
        Schema::create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->integer('recipient_id');
            $table->enum('recipient_type', ['user', 'group', 'centre'])->default('user');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->index(['message_id', 'recipient_id', 'is_read']);
        });

        // 3. NOTIFICATIONS - System notifications (preserves current structure)
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 4. LETTERS - Official correspondence (preserves current structure)
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_number')->unique();
            $table->string('letter_type');
            $table->string('recipient_name');
            $table->string('recipient_address');
            $table->string('subject');
            $table->text('content');
            $table->integer('created_by');
            $table->string('centre_id', 10);
            $table->enum('status', ['draft', 'sent', 'delivered', 'responded'])->default('draft');
            $table->date('date_created');
            $table->date('date_sent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['letter_type', 'status', 'centre_id']);
            $table->index('date_created');
        });

        // 5. LETTER TEMPLATES - Template management (preserves current structure)
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->string('template_type');
            $table->text('template_content');
            $table->json('required_fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('created_by');
            $table->timestamps();
            
            $table->index(['template_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_templates');
        Schema::dropIfExists('letters');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('message_recipients');
        Schema::dropIfExists('messages');
    }
}