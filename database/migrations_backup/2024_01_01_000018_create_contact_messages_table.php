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
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('sender_phone')->nullable();
            $table->string('message_subject');
            $table->text('message_body');
            $table->enum('message_category', ['general', 'complaint', 'suggestion', 'support'])->default('general');
            $table->enum('message_status', ['new', 'read', 'replied', 'resolved'])->default('new');
            $table->text('admin_reply')->nullable();
            $table->unsignedBigInteger('replied_by')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
            
            $table->index(['message_status']);
            $table->index(['message_category']);
            $table->index(['sender_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};