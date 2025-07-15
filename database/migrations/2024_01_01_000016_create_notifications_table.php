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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_title');
            $table->text('notification_message');
            $table->enum('notification_type', ['info', 'warning', 'success', 'error'])->default('info');
            $table->unsignedBigInteger('user_id');
            $table->string('user_type')->default('user');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('notification_data')->nullable();
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['user_type']);
            $table->index(['is_read']);
            $table->index(['notification_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};