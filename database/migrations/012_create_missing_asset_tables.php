<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Create missing asset-related tables
     */
    public function up(): void
    {
        // Create asset_categories table
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('depreciation_rate', 5, 2)->default(20.00);
            $table->timestamps();
            
            $table->index('parent_id');
            $table->index('is_active');
            $table->index('code');
        });

        // Add foreign key after table creation to avoid circular dependency
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('asset_categories')->nullOnDelete();
        });

        // Create asset_movements table
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->enum('type', ['assignment', 'transfer', 'return', 'disposal', 'maintenance'])->default('assignment');
            $table->unsignedBigInteger('from_user')->nullable();
            $table->unsignedBigInteger('to_user')->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('performed_by');
            $table->datetime('movement_date');
            $table->enum('status', ['pending', 'approved', 'completed', 'cancelled'])->default('completed');
            $table->text('notes')->nullable();
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['asset_id']);
            $table->index(['type']);
            $table->index(['movement_date']);
            $table->index(['from_user']);
            $table->index(['to_user']);
            $table->index(['performed_by']);
            $table->index(['centre_id']);
        });

        // Create asset_locations table
        Schema::create('asset_locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_name');
            $table->string('location_code')->unique();
            $table->text('description')->nullable();
            $table->string('centre_id');
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->string('room')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('responsible_person')->nullable();
            $table->timestamps();
            
            $table->index(['centre_id']);
            $table->index(['is_active']);
            $table->index(['responsible_person']);
        });

        // Seed some basic asset categories
        $this->seedBasicAssetCategories();
    }

    /**
     * Seed basic asset categories
     */
    private function seedBasicAssetCategories()
    {
        $categories = [
            [
                'name' => 'Computer Equipment',
                'code' => 'COMP',
                'description' => 'Computers, laptops, and related equipment',
                'icon' => 'fas fa-laptop',
                'color' => '#007bff',
                'is_active' => true,
                'depreciation_rate' => 25.00
            ],
            [
                'name' => 'Furniture',
                'code' => 'FURN',
                'description' => 'Office furniture and fixtures',
                'icon' => 'fas fa-couch',
                'color' => '#6c757d',
                'is_active' => true,
                'depreciation_rate' => 10.00
            ],
            [
                'name' => 'Medical Equipment',
                'code' => 'MED',
                'description' => 'Medical and therapeutic equipment',
                'icon' => 'fas fa-heartbeat',
                'color' => '#dc3545',
                'is_active' => true,
                'depreciation_rate' => 15.00
            ],
            [
                'name' => 'Rehabilitation Tools',
                'code' => 'REHAB',
                'description' => 'Tools and equipment for rehabilitation activities',
                'icon' => 'fas fa-tools',
                'color' => '#28a745',
                'is_active' => true,
                'depreciation_rate' => 20.00
            ],
            [
                'name' => 'Educational Materials',
                'code' => 'EDU',
                'description' => 'Books, materials, and educational aids',
                'icon' => 'fas fa-book',
                'color' => '#ffc107',
                'is_active' => true,
                'depreciation_rate' => 30.00
            ],
            [
                'name' => 'Safety Equipment',
                'code' => 'SAFE',
                'description' => 'Safety and emergency equipment',
                'icon' => 'fas fa-shield-alt',
                'color' => '#fd7e14',
                'is_active' => true,
                'depreciation_rate' => 15.00
            ]
        ];

        foreach ($categories as $category) {
            DB::table('asset_categories')->insert(array_merge($category, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_locations');
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('asset_categories');
    }
};