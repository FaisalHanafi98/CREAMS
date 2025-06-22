<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            
            // Personal Information
            $table->string('name')->nullable(false);
            $table->string('email')->nullable(false);
            $table->string('phone')->nullable(true);

            // Message Details
            $table->enum('reason', [
                'services', 
                'support', 
                'partnership', 
                'volunteer', 
                'general',
                'other'
            ])->nullable(false);
            
            $table->text('message')->nullable(false);

            // Additional Metadata
            $table->enum('status', [
                'new', 
                'read', 
                'replied', 
                'archived', 
                'spam'
            ])->default('new');

            $table->unsignedBigInteger('assigned_to')->nullable(true);
            $table->timestamp('replied_at')->nullable(true);

            // IP and User Agent for tracking
            $table->ipAddress('ip_address')->nullable(true);
            $table->text('user_agent')->nullable(true);

            // Timestamps
            $table->timestamps();

            // Foreign key constraint (optional, only if you have a users table)
            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // Indexes for performance
            $table->index('email');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact_messages');
    }
};