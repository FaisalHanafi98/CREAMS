<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        try {
            Log::info('Starting users table creation/modification');
            
            Schema::create('users', function (Blueprint $table) {
                $table->bigIncrements('id');  
                $table->string('iium_id', 8)->unique();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('status')->default('active');
                $table->rememberToken();
                $table->string('role');
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('position')->nullable();
                $table->string('centre_id')->nullable();
                $table->string('centre_location')->nullable(); 
                $table->string('user_avatar')->nullable();
                $table->string('user_activity_1')->nullable();
                $table->string('user_activity_2')->nullable();
                $table->datetime('user_last_accessed_at')->nullable();
                $table->text('about')->nullable();
                $table->text('review')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->timestamps();
            });
            
            Log::info('Users table created/modified successfully');
        } catch (\Exception $e) {
            Log::error('Error creating/modifying users table', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
