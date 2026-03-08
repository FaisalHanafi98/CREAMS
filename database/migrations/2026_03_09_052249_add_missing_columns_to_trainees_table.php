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
            if (!Schema::hasColumn('trainees', 'medical_history')) {
                $table->text('medical_history')->nullable()->after('emergency_contact_relationship');
            }
            if (!Schema::hasColumn('trainees', 'additional_notes')) {
                $table->text('additional_notes')->nullable()->after('medical_history');
            }
            if (!Schema::hasColumn('trainees', 'avatar')) {
                $table->string('avatar')->nullable()->after('additional_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            $table->dropColumn(['medical_history', 'additional_notes', 'avatar']);
        });
    }
};
