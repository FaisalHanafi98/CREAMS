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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Profile Settings
            $table->text('bio')->nullable();
            $table->string('position')->nullable();
            $table->string('phone')->nullable();
            
            // Notification Preferences
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('reminder_emails')->default(true);
            $table->boolean('weekly_reports')->default(true);
            $table->boolean('activity_updates')->default(true);
            $table->boolean('trainee_progress')->default(true);
            $table->boolean('system_alerts')->default(true);
            
            // Application Preferences
            $table->enum('theme', ['light', 'dark', 'auto'])->default('light');
            $table->string('language', 5)->default('en');
            $table->string('date_format')->default('DD/MM/YYYY');
            $table->enum('time_format', ['12hr', '24hr'])->default('24hr');
            $table->enum('first_day_of_week', ['sunday', 'monday'])->default('monday');
            $table->string('default_view')->default('dashboard');
            $table->integer('items_per_page')->default(25);
            $table->integer('auto_logout')->default(30); // minutes
            
            // Security Settings
            $table->boolean('two_factor_auth')->default(false);
            $table->integer('session_timeout')->default(30); // minutes
            $table->boolean('login_alerts')->default(true);
            $table->boolean('api_access')->default(false);
            $table->boolean('data_export')->default(true);
            
            // Privacy Settings
            $table->enum('profile_visibility', ['everyone', 'staff_only', 'admin_only'])->default('staff_only');
            $table->boolean('show_activity_status')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};