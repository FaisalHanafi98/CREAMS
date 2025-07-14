<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Log::info('Starting notifications table creation');
            
            if (!Schema::hasTable('notifications')) {
                Schema::create('notifications', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id');
                    $table->string('user_type');
                    $table->string('type');
                    $table->string('title');
                    $table->text('content');
                    $table->json('data')->nullable();
                    $table->boolean('read')->default(false);
                    $table->timestamp('read_at')->nullable();
                    $table->timestamps();
                    
                    // Indexes
                    $table->index(['user_id', 'user_type']);
                    $table->index('read');
                    $table->index('type');
                    $table->index('created_at'); // For sorting by date
                });
                
                Log::info('Notifications table created successfully');
            } else {
                Log::info('Notifications table already exists, skipping creation');
                
                // Check if indexes exist and add if missing
                $this->ensureIndexes();
            }
        } catch (\Exception $e) {
            Log::error('Error creating notifications table', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            Schema::dropIfExists('notifications');
            Log::info('Notifications table dropped successfully');
        } catch (\Exception $e) {
            Log::error('Error dropping notifications table', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Ensure all required indexes exist.
     *
     * @return void
     */
    private function ensureIndexes()
    {
        try {
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $schemaManager->listTableIndexes('notifications');
            $indexNames = array_keys($indexes);
            
            if (!in_array('notifications_user_id_user_type_index', $indexNames)) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->index(['user_id', 'user_type']);
                });
                Log::info('Added user index to notifications table');
            }
            
            if (!in_array('notifications_read_index', $indexNames)) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->index('read');
                });
                Log::info('Added read index to notifications table');
            }
            
            if (!in_array('notifications_type_index', $indexNames)) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->index('type');
                });
                Log::info('Added type index to notifications table');
            }
            
            if (!in_array('notifications_created_at_index', $indexNames)) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->index('created_at');
                });
                Log::info('Added created_at index to notifications table');
            }
        } catch (\Exception $e) {
            Log::warning('Error ensuring indexes on notifications table', [
                'message' => $e->getMessage()
            ]);
            // Continue execution, as this is just an enhancement
        }
    }
};