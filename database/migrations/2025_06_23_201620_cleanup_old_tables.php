<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old activity-related tables if they exist
        Schema::dropIfExists('trainee_activities');
        Schema::dropIfExists('rehabilitation_activities');
        Schema::dropIfExists('rehabilitation_objectives');
        Schema::dropIfExists('rehabilitation_materials');
        Schema::dropIfExists('rehabilitation_schedules');
        Schema::dropIfExists('rehabilitation_participants');
        
        // Drop old assets table if exists (keeping assets_enhanced)
        if (Schema::hasTable('assets') && Schema::hasTable('assets_enhanced')) {
            Schema::dropIfExists('assets');
        }
        
        // Rename assets_enhanced to assets if needed
        if (Schema::hasTable('assets_enhanced') && !Schema::hasTable('assets')) {
            Schema::rename('assets_enhanced', 'assets');
        }
    }

    public function down(): void
    {
        // Not reversible - old table structures are lost
    }
};