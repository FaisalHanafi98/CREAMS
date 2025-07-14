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
        Schema::create('asset_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Hierarchy and organization
            $table->string('centre_id');
            $table->unsignedBigInteger('parent_location_id')->nullable();
            
            // Location details
            $table->enum('location_type', [
                'building', 'floor', 'room', 'storage', 
                'outdoor', 'vehicle', 'workshop', 'office'
            ])->default('room');
            
            // Physical address components
            $table->string('building', 100)->nullable();
            $table->string('floor', 50)->nullable();
            $table->string('room_number', 50)->nullable();
            
            // Capacity management
            $table->integer('capacity')->nullable()->comment('Maximum number of assets');
            
            // GPS coordinates (optional)
            $table->decimal('coordinates_lat', 10, 8)->nullable();
            $table->decimal('coordinates_lng', 11, 8)->nullable();
            
            // Access control
            $table->enum('access_level', [
                'public', 'restricted', 'secure', 'authorized_only'
            ])->default('public');
            
            // Contact and management
            $table->string('contact_person', 100)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['centre_id', 'is_active']);
            $table->index(['parent_location_id']);
            $table->index(['location_type']);
            $table->index(['building', 'floor', 'room_number']);
            
            // Foreign key constraints
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
            $table->foreign('parent_location_id')->references('id')->on('asset_locations')->onDelete('set null');
        });

        // Add foreign key constraint to assets_enhanced table
        Schema::table('assets_enhanced', function (Blueprint $table) {
            $table->foreign('location_id')->references('id')->on('asset_locations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key from assets_enhanced first
        Schema::table('assets_enhanced', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
        });
        
        Schema::dropIfExists('asset_locations');
    }
};