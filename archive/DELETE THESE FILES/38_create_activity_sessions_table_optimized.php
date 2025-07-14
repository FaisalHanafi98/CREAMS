<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('activity_sessions')) {
            Log::info('Creating activity_sessions table');
            
            Schema::create('activity_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->unsignedBigInteger('teacher_id');
                $table->date('date')->index();
                $table->time('start_time');
                $table->integer('duration'); // in minutes
                $table->string('location');
                $table->integer('max_capacity')->default(20);
                $table->enum('status', ['active', 'cancelled', 'completed'])->default('active');
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
                $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
                
                // Indexes
                $table->index(['date', 'start_time']);
                $table->index(['teacher_id', 'date']);
                $table->index(['activity_id', 'status']);
            });
            
            Log::info('activity_sessions table created successfully');
        } else {
            // Table exists, check if we need to modify it
            Log::info('activity_sessions table already exists, checking for needed modifications');
            
            // Add any needed column updates here
            if (!Schema::hasColumn('activity_sessions', 'date')) {
                Schema::table('activity_sessions', function (Blueprint $table) {
                    $table->date('date')->index()->after('teacher_id');
                });
                Log::info('Added date column to activity_sessions table');
            }
            
            if (!Schema::hasColumn('activity_sessions', 'duration')) {
                Schema::table('activity_sessions', function (Blueprint $table) {
                    $table->integer('duration')->after('start_time');  // in minutes
                });
                Log::info('Added duration column to activity_sessions table');
            }
            
            if (!Schema::hasColumn('activity_sessions', 'status')) {
                Schema::table('activity_sessions', function (Blueprint $table) {
                    $table->enum('status', ['active', 'cancelled', 'completed'])->default('active')->after('max_capacity');
                });
                Log::info('Added status column to activity_sessions table');
            }
            
            // Add any additional column modifications if needed
        }
    }

    public function down(): void
    {
        // This is safer than just dropping the table since it might have existing data
        if (Schema::hasColumn('activity_sessions', 'date')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                $table->dropColumn('date');
            });
        }
        
        if (Schema::hasColumn('activity_sessions', 'duration')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                $table->dropColumn('duration');
            });
        }
        
        if (Schema::hasColumn('activity_sessions', 'status')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
        
        // If you do want to drop the entire table:
        // Schema::dropIfExists('activity_sessions');
    }
};