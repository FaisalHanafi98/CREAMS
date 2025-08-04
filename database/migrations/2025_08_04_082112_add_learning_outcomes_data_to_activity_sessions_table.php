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
        Schema::table('activity_sessions', function (Blueprint $table) {
            // Add session-specific learning outcomes data storage
            $table->json('learning_outcomes_data')->nullable()->after('session_description')
                ->comment('JSON field storing session-specific learning outcome assignments, weights, and assessment methods');
            
            // Add outcome completion tracking
            $table->decimal('outcome_completion_rate', 5, 2)->nullable()->after('learning_outcomes_data')
                ->comment('Percentage of assigned learning outcomes completed by enrolled trainees');
            
            // Add last progress update timestamp
            $table->timestamp('last_progress_update')->nullable()->after('outcome_completion_rate')
                ->comment('Last time learning outcome progress was updated for this session');
            
            // Add index for performance
            $table->index(['outcome_completion_rate', 'session_date'], 'idx_session_outcome_completion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_sessions', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex('idx_session_outcome_completion');
            
            // Drop columns
            $table->dropColumn([
                'learning_outcomes_data',
                'outcome_completion_rate', 
                'last_progress_update'
            ]);
        });
    }
};
