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
        Schema::table('volunteers', function (Blueprint $table) {
            // Remove occupation column
            $table->dropColumn('occupation');

            // Add missing approval fields
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            // Add foreign key for reviewed_by
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteers', function (Blueprint $table) {
            // Drop foreign key and approval fields
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['reviewed_by', 'review_notes', 'reviewed_at']);

            // Add back occupation column
            $table->string('occupation', 255)->nullable();
        });
    }
};
