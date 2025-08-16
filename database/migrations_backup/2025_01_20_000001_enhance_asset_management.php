<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class EnhanceAssetManagement extends Migration
{
    public function up()
    {
        // Asset categories with hierarchy
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('depreciation_rate')->default(20);
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('asset_categories')->nullOnDelete();
            $table->index('parent_id');
        });

        // Enhanced assets table
        Schema::create('assets_enhanced', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('centre_id');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->integer('warranty_months')->default(12);
            $table->date('warranty_expiry')->nullable();
            $table->enum('condition', ['new', 'good', 'fair', 'poor', 'broken'])->default('new');
            $table->enum('status', ['available', 'in_use', 'maintenance', 'disposed'])->default('available');
            $table->string('location')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->date('assigned_date')->nullable();
            $table->decimal('depreciation_rate', 5, 2)->default(20.00);
            $table->decimal('current_value', 10, 2)->nullable();
            $table->json('specifications')->nullable();
            $table->json('images')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('asset_categories');
            $table->foreign('centre_id')->references('id')->on('centres');
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            
            $table->index(['status', 'condition']);
            $table->index(['centre_id', 'category_id']);
            $table->index('warranty_expiry');
        });
        
        // Asset maintenance records
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->enum('type', ['preventive', 'corrective', 'inspection']);
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])
                  ->default('scheduled');
            $table->string('performed_by')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->text('description');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('asset_id')->references('id')->on('assets_enhanced')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            
            $table->index(['asset_id', 'status']);
            $table->index('scheduled_date');
        });
        
        // Asset movements/history
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->enum('type', ['assignment', 'return', 'transfer', 'maintenance', 'disposal']);
            $table->unsignedBigInteger('from_user')->nullable();
            $table->unsignedBigInteger('to_user')->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('performed_by');
            $table->timestamp('movement_date');
            $table->timestamps();
            
            $table->foreign('asset_id')->references('id')->on('assets_enhanced')->onDelete('cascade');
            $table->foreign('from_user')->references('id')->on('users')->nullOnDelete();
            $table->foreign('to_user')->references('id')->on('users')->nullOnDelete();
            $table->foreign('performed_by')->references('id')->on('users');
            
            $table->index(['asset_id', 'movement_date']);
        });

        // Seed default asset categories
        $this->seedAssetCategories();

        // Migrate existing data if applicable
        $this->migrateExistingAssets();
    }
    
    public function down()
    {
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('asset_maintenance');
        Schema::dropIfExists('assets_enhanced');
        Schema::dropIfExists('asset_categories');
    }

    private function seedAssetCategories()
    {
        $categories = [
            [
                'name' => 'Medical Equipment',
                'code' => 'MED',
                'description' => 'Medical and healthcare equipment',
                'icon' => 'fas fa-heartbeat',
                'color' => '#e74c3c',
                'depreciation_rate' => 15
            ],
            [
                'name' => 'Rehabilitation Equipment',
                'code' => 'REH',
                'description' => 'Rehabilitation and therapy equipment',
                'icon' => 'fas fa-procedures',
                'color' => '#3498db',
                'depreciation_rate' => 20
            ],
            [
                'name' => 'Educational Technology',
                'code' => 'EDU',
                'description' => 'Educational and learning technology',
                'icon' => 'fas fa-graduation-cap',
                'color' => '#9b59b6',
                'depreciation_rate' => 25
            ],
            [
                'name' => 'Computer Equipment',
                'code' => 'COM',
                'description' => 'Computers, laptops, and IT equipment',
                'icon' => 'fas fa-laptop',
                'color' => '#34495e',
                'depreciation_rate' => 30
            ],
            [
                'name' => 'Furniture',
                'code' => 'FUR',
                'description' => 'Office and classroom furniture',
                'icon' => 'fas fa-chair',
                'color' => '#95a5a6',
                'depreciation_rate' => 10
            ],
            [
                'name' => 'Vehicles',
                'code' => 'VEH',
                'description' => 'Transportation vehicles',
                'icon' => 'fas fa-car',
                'color' => '#e67e22',
                'depreciation_rate' => 20
            ],
            [
                'name' => 'Audio Visual',
                'code' => 'AV',
                'description' => 'Audio visual equipment and systems',
                'icon' => 'fas fa-video',
                'color' => '#1abc9c',
                'depreciation_rate' => 25
            ],
            [
                'name' => 'Safety Equipment',
                'code' => 'SAF',
                'description' => 'Safety and security equipment',
                'icon' => 'fas fa-shield-alt',
                'color' => '#f39c12',
                'depreciation_rate' => 15
            ]
        ];

        foreach ($categories as $category) {
            DB::table('asset_categories')->insert(array_merge($category, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    private function migrateExistingAssets()
    {
        try {
            if (Schema::hasTable('assets')) {
                $existingAssets = DB::table('assets')->get();
                
                foreach ($existingAssets as $asset) {
                    // Generate asset code if not exists
                    $assetCode = $asset->asset_code ?? $this->generateAssetCode($asset->category_id ?? 1);
                    
                    // Map old condition values to new enum
                    $condition = $this->mapCondition($asset->condition ?? 'good');
                    
                    // Map old status values to new enum
                    $status = $this->mapStatus($asset->status ?? 'available');
                    
                    DB::table('assets_enhanced')->insert([
                        'id' => $asset->id,
                        'asset_code' => $assetCode,
                        'name' => $asset->name ?? 'Unknown Asset',
                        'description' => $asset->description,
                        'category_id' => $this->mapCategoryId($asset->category_id ?? null),
                        'centre_id' => $asset->centre_id ?? 1,
                        'brand' => $asset->brand,
                        'model' => $asset->model,
                        'serial_number' => $asset->serial_number,
                        'purchase_price' => $asset->purchase_price,
                        'purchase_date' => $asset->purchase_date,
                        'warranty_months' => $asset->warranty_months ?? 12,
                        'warranty_expiry' => $asset->warranty_expiry,
                        'condition' => $condition,
                        'status' => $status,
                        'location' => $asset->location,
                        'assigned_to' => $asset->assigned_to,
                        'assigned_date' => $asset->assigned_date,
                        'depreciation_rate' => 20.00,
                        'current_value' => $asset->current_value ?? $asset->purchase_price,
                        'specifications' => $asset->specifications ? json_encode(json_decode($asset->specifications, true) ?: []) : json_encode([]),
                        'images' => $asset->images ? json_encode(json_decode($asset->images, true) ?: []) : json_encode([]),
                        'notes' => $asset->notes,
                        'created_by' => $asset->created_by ?? 1,
                        'created_at' => $asset->created_at ?? now(),
                        'updated_at' => $asset->updated_at ?? now()
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Asset migration had issues: ' . $e->getMessage());
        }
    }

    private function generateAssetCode($categoryId)
    {
        $prefix = 'AST';
        $year = date('y');
        $sequence = DB::table('assets_enhanced')->count() + 1;
        
        return sprintf("{$prefix}-{$year}-%05d", $sequence);
    }

    private function mapCondition($oldCondition)
    {
        $conditionMap = [
            'new' => 'new',
            'excellent' => 'new',
            'good' => 'good',
            'fair' => 'fair',
            'poor' => 'poor',
            'broken' => 'broken',
            'damaged' => 'poor'
        ];
        
        return $conditionMap[strtolower($oldCondition)] ?? 'good';
    }

    private function mapStatus($oldStatus)
    {
        $statusMap = [
            'available' => 'available',
            'in_use' => 'in_use',
            'assigned' => 'in_use',
            'maintenance' => 'maintenance',
            'repair' => 'maintenance',
            'disposed' => 'disposed',
            'decommissioned' => 'disposed'
        ];
        
        return $statusMap[strtolower($oldStatus)] ?? 'available';
    }

    private function mapCategoryId($oldCategoryId)
    {
        // Try to find matching category, fallback to first category
        $defaultCategory = DB::table('asset_categories')->first();
        return $defaultCategory ? $defaultCategory->id : 1;
    }
}