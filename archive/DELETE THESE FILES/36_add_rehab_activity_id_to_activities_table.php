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
        Schema::table('rehabilitation_activities', function (Blueprint $table) {
            // Add any new columns or modifications here
            // For example:
            // $table->string('new_column')->nullable()->after('existing_column');
            
            // If you want to add a unique constraint
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rehabilitation_activities', function (Blueprint $table) {
            // Reverse the changes
            // For example:
            // $table->dropColumn('new_column');
            $table->dropUnique('rehabilitation_activities_name_unique');
        });
    }
};