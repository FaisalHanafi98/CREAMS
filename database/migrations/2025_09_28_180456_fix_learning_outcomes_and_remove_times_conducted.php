<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix learning outcomes: replace \n with spaces
        DB::statement("UPDATE activities SET learning_outcomes = REPLACE(learning_outcomes, '\n', ' ')");

        // Remove times_conducted column since we'll count from sessions
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('times_conducted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add times_conducted column
        Schema::table('activities', function (Blueprint $table) {
            $table->integer('times_conducted')->default(0);
        });

        // Note: We can't easily revert the learning outcomes formatting
    }
};
