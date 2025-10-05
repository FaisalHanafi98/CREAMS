# CREAMS Migration & Seeder Revamp Action Plan

## Overview
This plan provides step-by-step instructions to revamp the CREAMS database migrations and seeders while preserving all real production data from Gombak Centre (centre_id='01').

## Prerequisites
- ✅ Current system is working and stable
- ✅ All real data is in Centre '01' (Gombak)
- ✅ Seeded data is in Centres '02'-'05'
- ✅ Full database backup capability

## Phase 1: Data Safety & Backup

### Step 1.1: Create Data Backup Scripts
```bash
# Create backup directory
mkdir -p database/backups/$(date +%Y%m%d)

# Full database backup
mysqldump -u root -p creams_db > database/backups/$(date +%Y%m%d)/full_backup.sql

# Real data specific backup (Gombak only)
mysqldump -u root -p creams_db \
  --where="centre_id='01' OR id IN (SELECT id FROM users WHERE centre_id='01')" \
  centres users assets staff_attendances > database/backups/$(date +%Y%m%d)/gombak_real_data.sql
```

### Step 1.2: Create Real Data Extraction Seeder
```php
<?php
// database/seeders/GombakDataExtractor.php
class GombakDataExtractor extends Seeder
{
    public function run(): void
    {
        $realData = [
            'centres' => DB::table('centres')->where('centre_id', '01')->get(),
            'users' => DB::table('users')->where('centre_id', '01')->get(),
            'assets' => DB::table('assets')->where('centre_id', '01')->get(),
            'staff_attendances' => DB::table('staff_attendances')
                ->whereIn('user_id', function($query) {
                    $query->select('id')->from('users')->where('centre_id', '01');
                })->get(),
        ];
        
        file_put_contents(
            database_path('real_data_backup.json'), 
            json_encode($realData, JSON_PRETTY_PRINT)
        );
    }
}
```

## Phase 2: New Migration Structure

### Step 2.1: Create Logical Migration Sequence
**New migration file naming:**
```
2025_01_01_000001_create_foundation_tables.php      // centres, users
2025_01_01_000002_create_reference_tables.php       // categories, types
2025_01_01_000003_create_client_tables.php          // trainees, volunteers  
2025_01_01_000004_create_service_tables.php         // activities, sessions, enrollments
2025_01_01_000005_create_attendance_tables.php      // staff_attendances, trainee_attendances
2025_01_01_000006_create_asset_tables.php           // assets, maintenance, locations
2025_01_01_000007_create_communication_tables.php   // messages, notifications
2025_01_01_000008_create_system_tables.php          // failed_jobs, sessions
2025_01_01_000009_add_foreign_keys.php              // all relationships
2025_01_01_000010_add_indexes_and_constraints.php   // performance optimizations
```

### Step 2.2: Foundation Tables Migration
```php
<?php
// 2025_01_01_000001_create_foundation_tables.php
return new class extends Migration
{
    public function up(): void
    {
        // CENTRES - Multi-tenant foundation
        Schema::create('centres', function (Blueprint $table) {
            $table->string('centre_id', 10)->primary();
            $table->string('centre_name')->unique();
            $table->text('centre_address')->nullable();
            $table->string('centre_phone', 20)->nullable();
            $table->string('centre_email')->unique();
            $table->string('centre_capacity', 10)->nullable();
            $table->string('centre_manager')->nullable();
            $table->string('centre_manager_contact', 20)->nullable();
            $table->enum('centre_status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->text('centre_description')->nullable();
            $table->json('centre_facilities')->nullable();
            $table->time('opening_time')->default('08:00:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('centre_status');
            $table->index('is_active');
        });

        // USERS - System users (staff, admins)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('iium_id', 50)->nullable()->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('education_level', 100)->nullable();
            $table->string('education_specialization')->nullable();
            $table->string('teaching_specialization')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('role', ['admin', 'supervisor', 'teacher', 'ajk'])->default('teacher');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->string('centre_id', 10)->nullable();
            $table->string('encrypted_id')->nullable();
            $table->string('avatar')->nullable();
            $table->string('position', 100)->nullable();
            $table->text('about')->nullable();
            $table->string('centre_location')->nullable();
            $table->text('bio')->nullable();
            $table->timestamp('user_last_accessed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('role');
            $table->index('status');
            $table->index('centre_id');
        });
    }
};
```

## Phase 3: Seeder Reorganization

### Step 3.1: Create Base Data Seeder
```php
<?php
// database/seeders/BaseDataSeeder.php
class BaseDataSeeder extends Seeder
{
    /**
     * Seed essential reference data required by all centres
     */
    public function run(): void
    {
        $this->call([
            ActivityCategorySeeder::class,
            AssetCategorySeeder::class,
            AssetTypeSeeder::class,
            LetterTemplateSeeder::class,
        ]);
    }
}
```

### Step 3.2: Create Real Data Restoration Seeder
```php
<?php
// database/seeders/GombakRealDataSeeder.php
class GombakRealDataSeeder extends Seeder
{
    /**
     * Restore real production data for Gombak Centre
     */
    public function run(): void
    {
        $this->command->info('🏥 Restoring real Gombak Centre data...');
        
        if (!file_exists(database_path('real_data_backup.json'))) {
            $this->command->error('Real data backup not found!');
            return;
        }
        
        $realData = json_decode(file_get_contents(database_path('real_data_backup.json')), true);
        
        // Restore centres
        foreach ($realData['centres'] as $centre) {
            DB::table('centres')->insert($centre);
        }
        
        // Restore users
        foreach ($realData['users'] as $user) {
            DB::table('users')->insert($user);
        }
        
        // Continue for other tables...
        
        $this->command->info('✅ Real Gombak data restored successfully!');
    }
}
```

### Step 3.3: Create Demo Data Seeder
```php
<?php
// database/seeders/DemoDataSeeder.php
class DemoDataSeeder extends Seeder
{
    /**
     * Create demo data for test centres (02-05)
     */
    public function run(): void
    {
        $this->command->info('🎭 Creating demo data for test centres...');
        
        $this->call([
            CentreSeeder::class,        // Creates centres 02-05
            UserSeeder::class,          // Demo staff for test centres
            TraineeSeeder::class,       // Demo trainees
            AssetSeeder::class,         // Demo assets
            ActivitySeeder::class,      // Demo activities
            // ... other demo seeders
        ]);
        
        $this->command->info('✅ Demo data created successfully!');
    }
}
```

## Phase 4: Database Consistency Fixes

### Step 4.1: Fix Missing Trainee Attendances Migration
```php
<?php
// 2025_01_01_000005_create_attendance_tables.php
Schema::create('trainee_attendances', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('trainee_id');
    $table->unsignedBigInteger('activity_id')->nullable();
    $table->unsignedBigInteger('session_id')->nullable();
    $table->date('attendance_date');
    $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent');
    $table->text('notes')->nullable();
    $table->unsignedBigInteger('marked_by_user_id')->nullable();
    $table->timestamp('marked_at')->nullable();
    $table->timestamps();
    
    $table->index('trainee_id');
    $table->index('activity_id');
    $table->index('session_id');
    $table->index('attendance_date');
    $table->index('status');
});
```

### Step 4.2: Data Type Standardization
```php
<?php
// 2025_01_01_000011_standardize_data_types.php
return new class extends Migration
{
    public function up(): void
    {
        // Fix centre_id consistency
        Schema::table('activities', function (Blueprint $table) {
            $table->string('centre_id', 10)->change();
        });
        
        Schema::table('asset_locations', function (Blueprint $table) {
            $table->string('centre_id', 10)->change();
        });
        
        // Standardize attendance status enums
        DB::statement("ALTER TABLE staff_attendances MODIFY status ENUM('present','absent','late','sick_leave','emergency_leave','authorized_leave') DEFAULT 'absent'");
        DB::statement("ALTER TABLE trainee_attendances MODIFY status ENUM('present','absent','late','excused') DEFAULT 'absent'");
    }
};
```

## Phase 5: Implementation Steps

### Step 5.1: Pre-Migration Checklist
```bash
# 1. Verify current system is stable
php artisan migrate:status

# 2. Run full backup
./scripts/backup_database.sh

# 3. Extract real data
php artisan db:seed --class=GombakDataExtractor

# 4. Test on development copy
mysqldump creams_db | mysql creams_db_test
```

### Step 5.2: Migration Execution Plan
```bash
# 1. Remove old migration files (after backing up)
mv database/migrations database/migrations_old

# 2. Add new migration files
mkdir database/migrations

# 3. Run fresh migration with data restoration
php artisan migrate:fresh --seed --class=BaseDataSeeder
php artisan db:seed --class=GombakRealDataSeeder
php artisan db:seed --class=DemoDataSeeder
```

### Step 5.3: Post-Migration Verification
```php
<?php
// tests/Feature/DatabaseIntegrityTest.php
class DatabaseIntegrityTest extends TestCase
{
    public function test_gombak_real_data_preserved()
    {
        // Verify Gombak centre exists
        $this->assertDatabaseHas('centres', ['centre_id' => '01']);
        
        // Verify real users exist
        $realUsers = DB::table('users')->where('centre_id', '01')->count();
        $this->assertGreaterThan(0, $realUsers);
        
        // Verify assets preserved
        $realAssets = DB::table('assets')->where('centre_id', '01')->count();
        $this->assertGreaterThan(0, $realAssets);
    }
    
    public function test_demo_centres_created()
    {
        $demoCentres = DB::table('centres')->whereIn('centre_id', ['02','03','04','05'])->count();
        $this->assertEquals(4, $demoCentres);
    }
    
    public function test_foreign_key_constraints()
    {
        // Test all foreign key relationships work
        $this->assertDatabaseHas('users', ['centre_id' => '01']);
        $this->assertDatabaseHas('activities', ['centre_id' => '01']);
        $this->assertDatabaseHas('assets', ['centre_id' => '01']);
    }
}
```

## Phase 6: Rollback Plan

### Emergency Rollback Procedure
```bash
# If migration fails, restore from backup
mysql -u root -p creams_db < database/backups/$(date +%Y%m%d)/full_backup.sql

# Restore old migrations
mv database/migrations database/migrations_new
mv database/migrations_old database/migrations

# Verify system functionality
php artisan migrate:status
php artisan serve
```

## Timeline Estimate

| Phase | Duration | Dependencies |
|-------|----------|--------------|
| Phase 1: Backup & Safety | 2-3 hours | - |
| Phase 2: New Migrations | 1-2 days | Phase 1 complete |
| Phase 3: Seeder Revamp | 1 day | Phase 2 complete |
| Phase 4: Consistency Fixes | 4-6 hours | Phase 3 complete |
| Phase 5: Implementation | 2-4 hours | All phases ready |
| Phase 6: Testing & Verification | 1 day | Phase 5 complete |

**Total Estimated Time: 4-6 days**

## Success Criteria

✅ All real Gombak data preserved
✅ All demo centres functional  
✅ Consistent schema structure
✅ All foreign keys working
✅ Proper migration sequencing
✅ Clean seeder organization
✅ Full test coverage passes

## Risk Mitigation

- **Multiple backup points** at each phase
- **Development environment testing** before production
- **Incremental approach** - can pause/rollback at any phase
- **Real data extraction** before any destructive operations
- **Automated verification tests** for data integrity

This plan ensures your real Gombak centre data is completely safe while creating a clean, maintainable database structure for future development.