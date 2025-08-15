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
        // First, drop the foreign key constraint
        Schema::table('volunteers', function (Blueprint $table) {
            $table->dropForeign('volunteers_centre_id_foreign');
        });
        
        // Then change the column type and add new foreign key
        Schema::table('volunteers', function (Blueprint $table) {
            // Change centre_id to string to match centres.centre_id
            $table->string('centre_id', 50)->nullable()->change();
            
            // Add the correct foreign key constraint
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteers', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['centre_id']);
            
            // Change centre_id back to unsignedBigInteger
            $table->unsignedBigInteger('centre_id')->nullable()->change();
            
            // Add back the old foreign key constraint
            $table->foreign('centre_id')->references('id')->on('centres')->onDelete('set null');
        });
    }
};
