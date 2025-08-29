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

        // 4. LETTERS - Official correspondence (updated structure)
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_id')->unique(); // Main identifier for letters
            $table->string('letter_name')->nullable();
            $table->datetime('letter_date')->nullable();
            $table->string('letter_title')->nullable();
            $table->string('letter_subject')->nullable();
            $table->text('letter_content')->nullable();
            $table->string('letter_type');
            $table->bigInteger('recipient_id')->unsigned()->default(0);
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('recipient_address')->nullable();
            $table->string('recipient_type', 100)->nullable();
            
            // Legacy fields (kept for compatibility)
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->date('date_created')->nullable();
            $table->date('date_sent')->nullable();
            
            // Template and status
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('letter_status', 50)->default('draft');
            $table->enum('status', ['draft', 'sent', 'delivered', 'responded'])->default('draft');
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            
            // File management
            $table->string('pdf_path', 500)->nullable();
            $table->bigInteger('pdf_file_size')->unsigned()->nullable();
            $table->string('letter_file_path', 500)->nullable();
            $table->string('pdf_filename')->nullable();
            $table->bigInteger('file_size_bytes')->unsigned()->nullable();
            $table->string('generated_file_type', 50)->nullable();
            $table->timestamp('generated_at')->nullable();
            
            // Metadata
            $table->json('letter_data')->nullable();
            $table->json('generation_metadata')->nullable();
            $table->text('notes')->nullable();
            
            // User tracking
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->string('centre_id', 10);
            $table->timestamps();
            
            $table->index(['letter_type', 'status', 'centre_id']);
            $table->index('date_created');
            $table->index(['centre_id', 'letter_status']);
        });

        // 5. LETTER TEMPLATES - Template management (preserves current structure)
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->text('template_description')->nullable();
            $table->string('template_type');
            $table->text('template_content');
            $table->json('template_variables')->nullable();
            $table->string('header_image_path')->nullable();
            $table->string('footer_image_path')->nullable();
            $table->text('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('centre_id', 10)->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->json('required_fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('created_by');
            $table->timestamps();
            
            $table->index(['template_type', 'is_active']);
            $table->index(['centre_id', 'is_active']);
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