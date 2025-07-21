<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixMessagesNotificationsModule extends Migration
{
    public function up()
    {
        // Fix messages table schema
        $this->fixMessagesTable();
        
        // Fix notifications table schema
        $this->fixNotificationsTable();
        
        // Create message recipients table for polymorphic relationships
        $this->createMessageRecipientsTable();
        
        // Create notification delivery table for tracking
        $this->createNotificationDeliveryTable();
        
        // Create message templates table
        $this->createMessageTemplatesTable();
        
        // Create message categories table
        $this->createMessageCategoriesTable();
        
        // Add performance indexes
        $this->addPerformanceIndexes();
    }
    
    protected function fixMessagesTable()
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                // Add missing columns
                if (!Schema::hasColumn('messages', 'message_type')) {
                    $table->enum('message_type', ['direct', 'broadcast', 'announcement', 'system'])
                          ->default('direct')
                          ->after('message_priority');
                }
                
                if (!Schema::hasColumn('messages', 'centre_id')) {
                    $table->unsignedBigInteger('centre_id')->nullable()->after('recipient_id');
                }
                
                if (!Schema::hasColumn('messages', 'parent_message_id')) {
                    $table->unsignedBigInteger('parent_message_id')->nullable()->after('centre_id');
                }
                
                if (!Schema::hasColumn('messages', 'message_category_id')) {
                    $table->unsignedBigInteger('message_category_id')->nullable()->after('parent_message_id');
                }
                
                if (!Schema::hasColumn('messages', 'scheduled_at')) {
                    $table->timestamp('scheduled_at')->nullable()->after('read_at');
                }
                
                if (!Schema::hasColumn('messages', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable()->after('scheduled_at');
                }
                
                if (!Schema::hasColumn('messages', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->after('delivered_at');
                }
                
                if (!Schema::hasColumn('messages', 'is_deleted')) {
                    $table->boolean('is_deleted')->default(false)->after('expires_at');
                }
                
                if (!Schema::hasColumn('messages', 'deleted_at')) {
                    $table->timestamp('deleted_at')->nullable()->after('is_deleted');
                }
                
                if (!Schema::hasColumn('messages', 'metadata')) {
                    $table->json('metadata')->nullable()->after('attachments');
                }
                
                // Modify existing columns if needed
                try {
                    $table->text('message_body')->change();
                } catch (\Exception $e) {
                    // Column might already be text type
                }
            });
        }
    }
    
    protected function fixNotificationsTable()
    {
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                // Add missing columns
                if (!Schema::hasColumn('notifications', 'notifiable_type')) {
                    $table->string('notifiable_type')->nullable()->after('user_type');
                }
                
                if (!Schema::hasColumn('notifications', 'notifiable_id')) {
                    $table->unsignedBigInteger('notifiable_id')->nullable()->after('notifiable_type');
                }
                
                if (!Schema::hasColumn('notifications', 'centre_id')) {
                    $table->unsignedBigInteger('centre_id')->nullable()->after('notifiable_id');
                }
                
                if (!Schema::hasColumn('notifications', 'notification_channel')) {
                    $table->enum('notification_channel', ['database', 'email', 'sms', 'push'])
                          ->default('database')
                          ->after('notification_type');
                }
                
                if (!Schema::hasColumn('notifications', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable()->after('read_at');
                }
                
                if (!Schema::hasColumn('notifications', 'failed_at')) {
                    $table->timestamp('failed_at')->nullable()->after('delivered_at');
                }
                
                if (!Schema::hasColumn('notifications', 'failure_reason')) {
                    $table->text('failure_reason')->nullable()->after('failed_at');
                }
                
                if (!Schema::hasColumn('notifications', 'retry_count')) {
                    $table->integer('retry_count')->default(0)->after('failure_reason');
                }
                
                if (!Schema::hasColumn('notifications', 'scheduled_at')) {
                    $table->timestamp('scheduled_at')->nullable()->after('retry_count');
                }
                
                if (!Schema::hasColumn('notifications', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->after('scheduled_at');
                }
                
                if (!Schema::hasColumn('notifications', 'priority')) {
                    $table->enum('priority', ['low', 'normal', 'high', 'urgent'])
                          ->default('normal')
                          ->after('expires_at');
                }
            });
        }
    }
    
    protected function createMessageRecipientsTable()
    {
        if (!Schema::hasTable('message_recipients')) {
            Schema::create('message_recipients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('message_id');
                $table->morphs('recipient'); // recipient_type, recipient_id
                $table->enum('recipient_type_specific', ['user', 'role', 'centre', 'group'])->default('user');
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->boolean('is_deleted')->default(false);
                $table->timestamp('deleted_at')->nullable();
                $table->json('delivery_metadata')->nullable();
                $table->timestamps();
                
                $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
                $table->index(['message_id', 'recipient_type', 'recipient_id'], 'msg_recipients_composite_idx');
                $table->index(['is_read', 'read_at'], 'msg_recipients_read_idx');
                $table->index(['is_deleted', 'deleted_at'], 'msg_recipients_deleted_idx');
            });
        }
    }
    
    protected function createNotificationDeliveryTable()
    {
        if (!Schema::hasTable('notification_delivery')) {
            Schema::create('notification_delivery', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('notification_id');
                $table->string('channel'); // email, sms, push, database
                $table->enum('status', ['pending', 'delivered', 'failed', 'cancelled'])->default('pending');
                $table->timestamp('attempted_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->text('failure_reason')->nullable();
                $table->integer('retry_count')->default(0);
                $table->json('delivery_data')->nullable(); // channel-specific data
                $table->timestamps();
                
                $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
                $table->index(['notification_id', 'channel']);
                $table->index(['status', 'attempted_at']);
                $table->index(['channel', 'status']);
            });
        }
    }
    
    protected function createMessageTemplatesTable()
    {
        if (!Schema::hasTable('message_templates')) {
            Schema::create('message_templates', function (Blueprint $table) {
                $table->id();
                $table->string('template_name');
                $table->string('template_subject');
                $table->text('template_body');
                $table->enum('template_type', ['email', 'sms', 'notification', 'announcement']);
                $table->json('template_variables')->nullable(); // Available variables
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('centre_id')->nullable();
                $table->timestamps();
                
                $table->index(['template_type', 'is_active']);
                $table->index(['centre_id', 'is_active']);
                $table->index('created_by');
            });
        }
    }
    
    protected function createMessageCategoriesTable()
    {
        if (!Schema::hasTable('message_categories')) {
            Schema::create('message_categories', function (Blueprint $table) {
                $table->id();
                $table->string('category_name');
                $table->string('category_description')->nullable();
                $table->string('category_color', 7)->nullable(); // Hex color
                $table->string('category_icon')->nullable();
                $table->boolean('is_system')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                
                $table->index(['is_active', 'sort_order']);
                $table->index('is_system');
            });
        }
    }
    
    protected function addPerformanceIndexes()
    {
        try {
            // Messages table indexes
            if (Schema::hasTable('messages')) {
                Schema::table('messages', function (Blueprint $table) {
                    $existingIndexes = $this->getExistingIndexes('messages');
                    
                    if (!in_array('messages_message_type_index', $existingIndexes)) {
                        $table->index('message_type');
                    }
                    
                    if (!in_array('messages_centre_id_index', $existingIndexes)) {
                        $table->index('centre_id');
                    }
                    
                    if (!in_array('messages_scheduled_delivered_index', $existingIndexes)) {
                        $table->index(['scheduled_at', 'delivered_at'], 'messages_scheduled_delivered_index');
                    }
                    
                    if (!in_array('messages_sender_created_index', $existingIndexes)) {
                        $table->index(['sender_id', 'created_at'], 'messages_sender_created_index');
                    }
                    
                    if (!in_array('messages_parent_message_index', $existingIndexes)) {
                        $table->index('parent_message_id');
                    }
                });
            }
            
            // Notifications table indexes
            if (Schema::hasTable('notifications')) {
                Schema::table('notifications', function (Blueprint $table) {
                    $existingIndexes = $this->getExistingIndexes('notifications');
                    
                    if (!in_array('notifications_notifiable_index', $existingIndexes)) {
                        $table->index(['notifiable_type', 'notifiable_id'], 'notifications_notifiable_index');
                    }
                    
                    if (!in_array('notifications_centre_id_index', $existingIndexes)) {
                        $table->index('centre_id');
                    }
                    
                    if (!in_array('notifications_channel_status_index', $existingIndexes)) {
                        $table->index(['notification_channel', 'is_read'], 'notifications_channel_status_index');
                    }
                    
                    if (!in_array('notifications_scheduled_index', $existingIndexes)) {
                        $table->index(['scheduled_at', 'delivered_at'], 'notifications_scheduled_index');
                    }
                    
                    if (!in_array('notifications_priority_created_index', $existingIndexes)) {
                        $table->index(['priority', 'created_at'], 'notifications_priority_created_index');
                    }
                });
            }
            
        } catch (\Exception $e) {
            \Log::warning('Could not add some indexes to messages/notifications tables: ' . $e->getMessage());
        }
    }
    
    protected function getExistingIndexes($table)
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            return array_column($indexes, 'Key_name');
        } catch (\Exception $e) {
            return [];
        }
    }
    
    public function down()
    {
        // Drop new tables
        Schema::dropIfExists('message_categories');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('notification_delivery');
        Schema::dropIfExists('message_recipients');
        
        // Remove added columns from existing tables
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $columnsToRemove = [
                    'notifiable_type', 'notifiable_id', 'centre_id', 'notification_channel',
                    'delivered_at', 'failed_at', 'failure_reason', 'retry_count',
                    'scheduled_at', 'expires_at', 'priority'
                ];
                
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('notifications', $column)) {
                        try {
                            $table->dropColumn($column);
                        } catch (\Exception $e) {
                            // Column might not exist or have dependencies
                        }
                    }
                }
            });
        }
        
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $columnsToRemove = [
                    'message_type', 'centre_id', 'parent_message_id', 'message_category_id',
                    'scheduled_at', 'delivered_at', 'expires_at', 'is_deleted',
                    'deleted_at', 'metadata'
                ];
                
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('messages', $column)) {
                        try {
                            $table->dropColumn($column);
                        } catch (\Exception $e) {
                            // Column might not exist or have dependencies
                        }
                    }
                }
            });
        }
    }
}