# CREAMS Consolidated Migration & Seeder Proposal

## Overview
Consolidate current 11+ migration files and 16+ seeder files into **7 logical modules**, each with 1 migration + 1 seeder pair. This follows your current naming conventions and Malaysian context requirements.

## Proposed Module Structure

### Module 1: Foundation Management
**Purpose**: Core system infrastructure (centres, users, system tables)
**Dependencies**: None (must run first)

```
01_CREAMSMigrationFoundationManagement.php
01_CREAMSSeederFoundationManagement.php
```

**Tables**: `centres`, `users`, `failed_jobs`, `personal_access_tokens`, `sessions`

### Module 2: Client Management  
**Purpose**: Service recipients and volunteer management
**Dependencies**: Foundation Management

```
02_CREAMSMigrationClientManagement.php
02_CREAMSSeederClientManagement.php
```

**Tables**: `trainees`, `volunteers`, `contact_messages`

### Module 3: Service Delivery Management
**Purpose**: Programs, activities, and service delivery
**Dependencies**: Foundation, Client Management

```
03_CREAMSMigrationServiceDeliveryManagement.php
03_CREAMSSeederServiceDeliveryManagement.php
```

**Tables**: `activity_categories`, `activities`, `activity_sessions`, `activity_enrollments`

### Module 4: Attendance Management
**Purpose**: All attendance tracking systems
**Dependencies**: Foundation, Client, Service Delivery Management

```
04_CREAMSMigrationAttendanceManagement.php
04_CREAMSSeederAttendanceManagement.php
```

**Tables**: `staff_attendances`, `trainee_attendances`, `session_attendance`, `attendance_alerts`

### Module 5: Asset Management
**Purpose**: Equipment, inventory, and maintenance
**Dependencies**: Foundation Management

```
05_CREAMSMigrationAssetManagement.php
05_CREAMSSeederAssetManagement.php
```

**Tables**: `asset_categories`, `asset_types`, `asset_locations`, `assets`, `asset_maintenance`, `asset_maintenance_history`, `asset_movements`

### Module 6: Communication Management
**Purpose**: Messages, notifications, and documentation
**Dependencies**: Foundation Management

```
06_CREAMSMigrationCommunicationManagement.php
06_CREAMSSeederCommunicationManagement.php
```

**Tables**: `messages`, `message_recipients`, `notifications`, `letters`, `letter_templates`

### Module 7: System Constraints
**Purpose**: Foreign keys, indexes, and constraints
**Dependencies**: All previous modules

```
07_CREAMSMigrationSystemConstraints.php
07_CREAMSSeederSystemConstraints.php
```

**Purpose**: Add all foreign key relationships and performance indexes

---

## Detailed Module Specifications

### Module 1: Foundation Management

#### Migration Structure
```php
<?php
// 01_CREAMSMigrationFoundationManagement.php
return new class extends Migration
{
    public function up(): void
    {
        // 1. CENTRES - Malaysian rehabilitation centres
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
            
            $table->index(['centre_status', 'is_active']);
        });

        // 2. USERS - Staff and administrative users
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
            
            $table->index(['centre_id', 'role', 'status']);
        });

        // 3. SYSTEM TABLES
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }
};
```

#### Seeder Structure
```php
<?php
// 01_CREAMSSeederFoundationManagement.php
class FoundationManagementSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏛️ Seeding Foundation Management (Centres & Users)...');
        
        // Seed Malaysian rehabilitation centres
        $this->seedMalaysianCentres();
        
        // Seed real Gombak staff (if preserving)
        if (file_exists(database_path('real_data_backup.json'))) {
            $this->seedRealGombakUsers();
        }
        
        // Seed demo users for other centres
        $this->seedDemoUsers();
    }

    private function seedMalaysianCentres(): void
    {
        $centres = [
            [
                'centre_id' => '01',
                'centre_name' => 'Gombak',
                'centre_address' => 'Jalan Gombak, 53100 Gombak, Selangor',
                'centre_phone' => '+603-6196-4000',
                'centre_email' => 'gombak@iium.edu.my',
                'centre_capacity' => '150',
                'centre_manager' => 'Prof. Dr. Mohd Roslan bin Mohd Nor',
                'centre_manager_contact' => '+603-6196-4001',
                'centre_description' => 'CREAMS Gombak Centre - Pusat rehabilitasi utama di IIUM yang menyediakan perkhidmatan pendidikan dan terapi keperluan khas yang komprehensif.',
                'centre_facilities' => json_encode([
                    'Pentadbiran Utama', 'Bilik Terapi', 'Bilik Integrasi Sensori', 'Makmal Komputer',
                    'Perpustakaan', 'Bilik Terapi Seni', 'Gimnasium', 'Bilik Terapi Muzik',
                    'Bilik Terapi Pertuturan', 'Bilik Terapi Pekerjaan', 'Pusat Penyelidikan'
                ]),
                'opening_time' => '08:00:00',
                'is_active' => true,
            ],
            [
                'centre_id' => '02',
                'centre_name' => 'Kuantan',
                'centre_address' => 'Jalan Sultan Ahmad Shah, 25300 Kuantan, Pahang',
                'centre_phone' => '+609-513-4000',
                'centre_email' => 'kuantan@creams.edu.my',
                'centre_capacity' => '120',
                'centre_manager' => 'Dr. Siti Nurhaliza binti Ahmad',
                'centre_manager_contact' => '+609-513-4001',
                'centre_description' => 'CREAMS Kuantan Centre - Pusat rehabilitasi wilayah yang menyediakan perkhidmatan pendidikan dan terapi keperluan khas untuk kanak-kanak dan remaja.',
                'centre_facilities' => json_encode([
                    'Bilik Terapi', 'Bilik Aktiviti', 'Perpustakaan Kecil', 'Bilik Komputer',
                    'Bilik Terapi Pertuturan', 'Ruang Rekreasi', 'Bilik Konseling'
                ]),
                'opening_time' => '08:00:00',
                'is_active' => true,
            ],
            // Continue for Johor Bahru, Kota Kinabalu, Nilai...
        ];

        foreach ($centres as $centre) {
            $centre['created_at'] = now();
            $centre['updated_at'] = now();
            DB::table('centres')->insert($centre);
        }
    }

    private function seedDemoUsers(): void
    {
        $faker = Faker::create('ms_MY');
        
        // Malaysian staff names by ethnicity
        $malayNames = [
            'Dr. Ahmad Fauzi bin Abdul Rahman',
            'Puan Siti Zaleha binti Mohamed',
            'Encik Mohd Hafiz bin Ibrahim',
            'Dr. Nurliyana binti Hassan'
        ];
        
        $chineseNames = [
            'Dr. Tan Wei Ming',
            'Puan Lim Hui Xin',
            'Encik Wong Jian Hao',
            'Dr. Lee Mei Ling'
        ];
        
        $indianNames = [
            'Dr. Raj Kumar a/l Suresh',
            'Puan Priya a/p Ganesh',
            'Encik Vijay a/l Ravi',
            'Dr. Lakshmi a/p Krishnan'
        ];

        // Create demo users with Malaysian context
        $centres = DB::table('centres')->where('centre_id', '!=', '01')->get();
        
        foreach ($centres as $centre) {
            // Create 5-8 staff per centre with Malaysian names and roles
            // Include proper Malaysian academic titles and positions
        }
    }
}
```

---

### Module 2: Client Management

#### Migration Structure
```php
<?php
// 02_CREAMSMigrationClientManagement.php
return new class extends Migration
{
    public function up(): void
    {
        // TRAINEES - Service recipients
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
            $table->string('trainee_id', 50)->unique();
            $table->string('trainee_first_name', 100);
            $table->string('trainee_last_name', 100);
            $table->string('trainee_email')->unique();
            $table->string('ic_number', 15)->unique();
            $table->date('trainee_date_of_birth');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('trainee_phone_number', 20)->nullable();
            $table->text('trainee_address')->nullable();
            $table->string('trainee_condition')->nullable();
            $table->string('centre_id', 10)->nullable();
            $table->string('centre_name')->nullable();
            $table->integer('course_id')->nullable();
            $table->enum('status', ['active', 'inactive', 'graduated'])->default('active');
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_relationship', 50)->nullable();
            $table->text('guardian_address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relationship', 50)->nullable();
            $table->boolean('photo_consent')->default(false);
            $table->boolean('services_consent')->default(false);
            $table->date('registration_date')->default(now());
            $table->timestamps();
            
            $table->index(['centre_id', 'status']);
            $table->index('ic_number');
        });

        // VOLUNTEERS - Community supporters
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('volunteer_id', 50)->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('occupation')->nullable();
            $table->string('skills')->nullable();
            $table->string('centre_id', 10)->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->text('motivation')->nullable();
            $table->date('registration_date')->default(now());
            $table->timestamps();
            
            $table->index(['centre_id', 'status']);
        });

        // CONTACT MESSAGES - Public inquiries
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('subject');
            $table->text('message');
            $table->enum('inquiry_type', ['general', 'services', 'volunteer', 'donation', 'other'])->default('general');
            $table->enum('status', ['new', 'read', 'replied', 'resolved'])->default('new');
            $table->string('centre_id', 10)->nullable();
            $table->integer('replied_by')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->text('reply_message')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'inquiry_type']);
        });
    }
};
```

#### Seeder Structure
```php
<?php
// 02_CREAMSSeederClientManagement.php
class ClientManagementSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Seeding Client Management (Trainees & Volunteers)...');
        
        $this->seedMalaysianTrainees();
        $this->seedMalaysianVolunteers();
        $this->seedContactMessages();
    }

    private function seedMalaysianTrainees(): void
    {
        $faker = Faker::create('ms_MY');
        
        // Malaysian disability conditions with proper terminology
        $conditions = [
            'Autism Spectrum Disorder (Gangguan Spektrum Autisme)',
            'Down Syndrome (Sindrom Down)',
            'Attention Deficit Hyperactivity Disorder (ADHD)',
            'Cerebral Palsy (Palsi Serebrum)',
            'Learning Disability (Ketidakupayaan Pembelajaran)',
            'Intellectual Disability (Ketidakupayaan Intelektual)',
            'Speech and Language Delay (Kelewatan Pertuturan dan Bahasa)',
            'Developmental Delay (Kelewatan Perkembangan)'
        ];

        // Malaysian names by ethnicity with proper cultural context
        $malayNames = [
            'male_first' => ['Muhammad', 'Ahmad', 'Mohd', 'Abdul', 'Ismail', 'Yusof', 'Ibrahim', 'Hassan', 'Omar', 'Farid'],
            'female_first' => ['Nur', 'Siti', 'Nor', 'Noor', 'Fatimah', 'Zainab', 'Aishah', 'Farah', 'Zulaikha', 'Aminah'],
            'surnames' => ['Abdullah', 'Ahmad', 'Mohamed', 'Ibrahim', 'Ismail', 'Hassan', 'Rahman', 'Othman', 'Ali', 'Omar']
        ];
        
        $chineseNames = [
            'male_first' => ['Wei Ming', 'Jian Hao', 'Jun Feng', 'Chen Wei', 'Yong Tao', 'Kun Xiang'],
            'female_first' => ['Mei Li', 'Hui Xin', 'Yan Ying', 'Qi Jing', 'Yue Fang', 'Lin Yu'],
            'surnames' => ['Tan', 'Lim', 'Lee', 'Wong', 'Ng', 'Cheong', 'Yap', 'Ong', 'Chin', 'Ho']
        ];
        
        $indianNames = [
            'male_first' => ['Raj Kumar', 'Suresh', 'Ravi', 'Vijay', 'Ganesh', 'Arun', 'Prakash'],
            'female_first' => ['Priya', 'Lakshmi', 'Devi', 'Shanti', 'Meena', 'Kavitha', 'Anjali'],
            'surnames' => ['Pillai', 'Naidu', 'Gopal', 'Nair', 'Singh', 'Sharma', 'Krishnan']
        ];

        // Generate realistic Malaysian IC numbers and addresses
        $centres = DB::table('centres')->get();
        
        foreach ($centres as $centre) {
            $traineeCount = rand(25, 45); // Realistic numbers per centre
            
            for ($i = 0; $i < $traineeCount; $i++) {
                // Generate Malaysian-specific trainee data with proper cultural context
            }
        }
    }
}
```

This consolidation approach:

1. **Reduces complexity**: From 11+ migrations to 7 clean modules
2. **Maintains Malaysian context**: Proper names, addresses, cultural considerations
3. **Preserves real data**: Special handling for Gombak centre data
4. **Follows naming conventions**: Current pattern maintained
5. **Logical grouping**: Related tables grouped by business function

Would you like me to continue with the remaining modules (Service Delivery, Attendance, Asset, Communication, and System Constraints)?