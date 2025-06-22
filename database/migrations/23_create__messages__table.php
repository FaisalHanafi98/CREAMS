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
            Log::info('Starting messages table creation');
            
            if (!Schema::hasTable('messages')) {
                Schema::create('messages', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('sender_id');
                    $table->string('sender_type');
                    $table->unsignedBigInteger('recipient_id');
                    $table->string('recipient_type');
                    $table->string('subject');
                    $table->text('content');
                    $table->boolean('read')->default(false);
                    $table->timestamp('read_at')->nullable();
                    $table->timestamps();
                    
                    // Indexes for faster queries
                    $table->index(['sender_id', 'sender_type']);
                    $table->index(['recipient_id', 'recipient_type']);
                    $table->index('read');
                    $table->index('created_at'); // For sorting by date
                });
                
                Log::info('Messages table created successfully');
            } else {
                Log::info('Messages table already exists, skipping creation');
                
                // Check if indexes exist and add if missing
                $this->ensureIndexes();
            }
        } catch (\Exception $e) {
            Log::error('Error creating messages table', [
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
            Schema::dropIfExists('messages');
            Log::info('Messages table dropped successfully');
        } catch (\Exception $e) {
            Log::error('Error dropping messages table', [
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
            $indexes = $schemaManager->listTableIndexes('messages');
            $indexNames = array_keys($indexes);
            
            if (!in_array('messages_sender_id_sender_type_index', $indexNames)) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->index(['sender_id', 'sender_type']);
                });
                Log::info('Added sender index to messages table');
            }
            
            if (!in_array('messages_recipient_id_recipient_type_index', $indexNames)) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->index(['recipient_id', 'recipient_type']);
                });
                Log::info('Added recipient index to messages table');
            }
            
            if (!in_array('messages_read_index', $indexNames)) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->index('read');
                });
                Log::info('Added read index to messages table');
            }
            
            if (!in_array('messages_created_at_index', $indexNames)) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->index('created_at');
                });
                Log::info('Added created_at index to messages table');
            }
        } catch (\Exception $e) {
            Log::warning('Error ensuring indexes on messages table', [
                'message' => $e->getMessage()
            ]);
            // Continue execution, as this is just an enhancement
        }
    }
};