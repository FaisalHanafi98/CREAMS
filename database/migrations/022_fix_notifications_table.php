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
        // Check if notifications table exists, if not create it
        if (!Schema::hasTable('notifications')) {
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
        } else {
            // If table exists, ensure it has all required columns
            Schema::table('notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('notifications', 'user_type')) {
                    $table->string('user_type')->default('user')->after('user_id');
                }
                if (!Schema::hasColumn('notifications', 'notification_data')) {
                    $table->json('notification_data')->nullable()->after('read_at');
                }
                if (!Schema::hasColumn('notifications', 'read_at')) {
                    $table->timestamp('read_at')->nullable()->after('is_read');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the table as it might have important data
        // Just remove the columns we added
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (Schema::hasColumn('notifications', 'user_type')) {
                    $table->dropColumn('user_type');
                }
                if (Schema::hasColumn('notifications', 'notification_data')) {
                    $table->dropColumn('notification_data');
                }
                if (Schema::hasColumn('notifications', 'read_at')) {
                    $table->dropColumn('read_at');
                }
            });
        }
    }
};