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
        Schema::table('notifications', function (Blueprint $table) {
            // Add columns expected by NotificationService if they don't exist
            if (!Schema::hasColumn('notifications', 'notification_title')) {
                $table->string('notification_title')->nullable()->after('type');
            }
            if (!Schema::hasColumn('notifications', 'notification_message')) {
                $table->text('notification_message')->nullable()->after('notification_title');
            }
            if (!Schema::hasColumn('notifications', 'notification_type')) {
                $table->string('notification_type')->nullable()->after('notification_message');
            }
            if (!Schema::hasColumn('notifications', 'notification_channel')) {
                $table->string('notification_channel')->default('database')->after('notification_type');
            }
            if (!Schema::hasColumn('notifications', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('notification_channel');
            }
            if (!Schema::hasColumn('notifications', 'centre_id')) {
                $table->string('centre_id', 10)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('notifications', 'priority')) {
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('centre_id');
            }
            if (!Schema::hasColumn('notifications', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('notifications', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('scheduled_at');
            }
            if (!Schema::hasColumn('notifications', 'notification_data')) {
                $table->json('notification_data')->nullable()->after('expires_at');
            }
            if (!Schema::hasColumn('notifications', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('notification_data');
            }
            if (!Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('delivered_at');
            }

            // Add indexes for performance
            $table->index(['user_id', 'is_read']);
            $table->index(['centre_id', 'created_at']);
            $table->index(['notification_type', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn([
                'notification_title',
                'notification_message',
                'notification_type',
                'notification_channel',
                'user_id',
                'centre_id',
                'priority',
                'scheduled_at',
                'expires_at',
                'notification_data',
                'delivered_at',
                'is_read'
            ]);
        });
    }
};
