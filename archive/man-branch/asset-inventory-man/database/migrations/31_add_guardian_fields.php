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
            Log::info('Starting migration to add guardian fields to trainees table');
            
            if (Schema::hasTable('trainees')) {
                Schema::table('trainees', function (Blueprint $table) {
                    // Guardian information
                    if (!Schema::hasColumn('trainees', 'guardian_name')) {
                        $table->string('guardian_name')->nullable();
                    }
                    if (!Schema::hasColumn('trainees', 'guardian_relationship')) {
                        $table->string('guardian_relationship')->nullable();
                    }
                    if (!Schema::hasColumn('trainees', 'guardian_phone')) {
                        $table->string('guardian_phone')->nullable();
                    }
                    if (!Schema::hasColumn('trainees', 'guardian_email')) {
                        $table->string('guardian_email')->nullable();
                    }
                    if (!Schema::hasColumn('trainees', 'guardian_address')) {
                        $table->text('guardian_address')->nullable();
                    }
                    
                    // Additional information
                    if (!Schema::hasColumn('trainees', 'medical_history')) {
                        $table->text('medical_history')->nullable();
                    }
                    if (!Schema::hasColumn('trainees', 'additional_notes')) {
                        $table->text('additional_notes')->nullable();
                    }
                    
                    // Emergency contact
                    if (!Schema::hasColumn('trainees', 'emergency_contact_name')) {
                        $table->string('emergency_contact_name')->nullable();
                    }
                    if (!Schema::hasColumn('trainees', 'emergency_contact_phone')) {
                        $table->string('emergency_contact_phone')->nullable();
                    }
                    if (!Schema::hasColumn('trainees', 'emergency_contact_relationship')) {
                        $table->string('emergency_contact_relationship')->nullable();
                    }
                });
                
                Log::info('Successfully added guardian fields to trainees table');
            } else {
                Log::warning('Trainees table does not exist, skipping migration');
            }
        } catch (\Exception $e) {
            Log::error('Error adding guardian fields to trainees table', [
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
            Log::info('Rolling back migration to remove guardian fields from trainees table');
            
            if (Schema::hasTable('trainees')) {
                Schema::table('trainees', function (Blueprint $table) {
                    // Guardian information
                    $columns = [
                        'guardian_name',
                        'guardian_relationship',
                        'guardian_phone',
                        'guardian_email',
                        'guardian_address',
                        'medical_history',
                        'additional_notes',
                        'emergency_contact_name',
                        'emergency_contact_phone',
                        'emergency_contact_relationship'
                    ];
                    
                    foreach ($columns as $column) {
                        if (Schema::hasColumn('trainees', $column)) {
                            $table->dropColumn($column);
                        }
                    }
                });
                
                Log::info('Successfully removed guardian fields from trainees table');
            } else {
                Log::warning('Trainees table does not exist, skipping rollback');
            }
        } catch (\Exception $e) {
            Log::error('Error removing guardian fields from trainees table', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
};