<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Fix contact_messages table column names
     * Module: Communication (Contact Form Fix)
     * Priority: 015 - Critical (Fix contact form error)
     */
    public function up(): void
    {
        // First, rename the existing columns
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->renameColumn('name', 'sender_name');
            $table->renameColumn('email', 'sender_email');
            $table->renameColumn('phone', 'sender_phone');
            $table->renameColumn('subject', 'message_subject');
            $table->renameColumn('message', 'message_body');
            $table->renameColumn('inquiry_type', 'message_category');
            $table->renameColumn('status', 'message_status');
            $table->renameColumn('replied_by', 'assigned_to');
        });
        
        // Then, add the missing columns
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('organization')->nullable()->after('sender_phone');
            $table->enum('urgency', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('message_body');
            $table->string('preferred_contact_method')->nullable()->after('urgency');
            $table->string('referrer')->nullable()->after('user_agent');
            $table->timestamp('submitted_at')->nullable()->after('referrer');
            $table->timestamp('response_sent_at')->nullable()->after('replied_at');
            $table->timestamp('resolved_at')->nullable()->after('response_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            // Reverse the column renames
            $table->renameColumn('sender_name', 'name');
            $table->renameColumn('sender_email', 'email');
            $table->renameColumn('sender_phone', 'phone');
            $table->renameColumn('message_subject', 'subject');
            $table->renameColumn('message_body', 'message');
            $table->renameColumn('message_category', 'inquiry_type');
            $table->renameColumn('message_status', 'status');
            $table->renameColumn('assigned_to', 'replied_by');
            
            // Drop the added columns
            $table->dropColumn([
                'organization',
                'urgency', 
                'preferred_contact_method',
                'referrer',
                'submitted_at',
                'response_sent_at',
                'resolved_at'
            ]);
        });
    }
};