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
        Schema::table('trainees', function (Blueprint $table) {
            // Check if the column does not already exist
            if (!Schema::hasColumn('trainees', 'course_id')) {
                $table->string('course_id')->nullable()->after('trainee_condition');
            }

            // Check if the foreign key does not already exist
            $foreignKeys = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableForeignKeys('trainees');
            
            $hasForeignKey = collect($foreignKeys)
                ->contains(function ($key) {
                    return $key->getColumns()[0] === 'course_id';
                });

            if (!$hasForeignKey) {
                $table->foreign('course_id')
                      ->references('course_id')
                      ->on('courses')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            // Only drop foreign key and column if they exist
            if (Schema::hasColumn('trainees', 'course_id')) {
                $table->dropForeign(['course_id']);
                $table->dropColumn('course_id');
            }
        });
    }
};