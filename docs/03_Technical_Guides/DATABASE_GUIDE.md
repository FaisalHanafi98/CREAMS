# CREAMS Database Comprehensive Guide

## Table of Contents
1. [Database Overview](#database-overview)
2. [Naming Conventions](#naming-conventions)
3. [Core Tables Structure](#core-tables-structure)
4. [Data Classification](#data-classification)
5. [Relationships & Dependencies](#relationships--dependencies)
6. [Migration Strategy](#migration-strategy)
7. [Real vs Seeded Data Analysis](#real-vs-seeded-data-analysis)

## Database Overview

**Database Name**: `creams_db`
**System**: CREAMS (Centre for Rehabilitation and Special Needs)
**Current State**: Production-ready with mixed real and seeded data
**Character Set**: utf8mb4_unicode_ci

## Naming Conventions

### Table Naming Patterns
- **Snake case**: All tables use lowercase with underscores
- **Plural forms**: Most tables are plural (e.g., `users`, `centres`, `activities`)
- **Descriptive names**: Clear, self-documenting table names
- **Consistent suffixes**: 
  - `_categories` for categorization tables
  - `_attendances` for attendance tracking
  - `_enrollments` for enrollment/registration data

### Column Naming Patterns
- **Snake case**: All columns use lowercase with underscores
- **Prefixed IDs**: Foreign keys prefixed with table name (e.g., `centre_id`, `activity_id`)
- **Descriptive names**: Clear purpose indication
- **Consistent patterns**:
  - `*_at` for timestamps
  - `is_*` for boolean flags
  - `*_date` for date fields
  - `*_time` for time fields

### ID Conventions
- **Primary Keys**: Auto-incrementing `bigint(20) unsigned` for most tables
- **Centre IDs**: String-based (`varchar(10)`) - e.g., '01', '02', '03'
- **Custom IDs**: Some tables have custom ID formats (e.g., `trainee_id`)

## Core Tables Structure

### 1. Foundation Tables (Must exist first)

#### `centres` - Multi-tenant Foundation
```sql
PRIMARY KEY: centre_id (varchar(10))
PURPOSE: Multi-tenant architecture foundation
REAL DATA: Centre '01' (Gombak) contains real staff/assets
SEEDED: Centres '02'-'05' are test data
```

**Key Columns:**
- `centre_id`: Primary identifier ('01', '02', etc.)
- `centre_name`: Display name (e.g., 'Gombak', 'Kuantan')
- `centre_facilities`: JSON array of facility types
- `attendance_policies`: JSON configuration for policies

#### `users` - Staff/Admin Management
```sql
PRIMARY KEY: id (bigint, auto-increment)
FOREIGN KEY: centre_id → centres.centre_id
PURPOSE: System users (staff, admins, teachers)
REAL DATA: Staff assigned to centre '01' (Gombak)
```

**Key Columns:**
- `iium_id`: University identifier (replaces deprecated staff_id)
- `role`: enum('admin','supervisor','teacher','ajk')
- `centre_id`: Determines access scope
- `encrypted_id`: For secure URL parameters

### 2. Client Management Tables

#### `trainees` - Service Recipients
```sql
PRIMARY KEY: id (bigint, auto-increment)
FOREIGN KEY: centre_id → centres.centre_id
PURPOSE: Individuals receiving rehabilitation services
DATA STATUS: Mixed real/seeded across centres
```

**Key Columns:**
- `trainee_id`: Custom identifier format
- `centre_id`: Determines service location
- `trainee_condition`: Disability/condition description
- Guardian contact information fields

### 3. Service Delivery Tables

#### `activities` - Service Programs
```sql
PRIMARY KEY: id (bigint, auto-increment)
FOREIGN KEY: centre_id → centres.centre_id, category_id, instructor_id
PURPOSE: Rehabilitation programs/services offered
```

#### `activity_sessions` - Service Instances
```sql
PRIMARY KEY: id (bigint, auto-increment)
FOREIGN KEY: activity_id → activities.id, instructor_id → users.id
PURPOSE: Individual service delivery sessions
```

#### `activity_enrollments` - Service Assignments
```sql
PRIMARY KEY: id (bigint, auto-increment)
FOREIGN KEY: activity_id → activities.id, trainee_id → trainees.id
PURPOSE: Tracks trainee participation in programs
```

### 4. Attendance & Monitoring Tables

#### `staff_attendances` - Staff Tracking
```sql
PRIMARY KEY: id (bigint, auto-increment)
FOREIGN KEY: user_id → users.id, centre_id → centres.centre_id
PURPOSE: Staff work attendance monitoring
```

**Key Status Values:** present, absent, late, half_day, leave

#### `trainee_attendances` - Service Attendance
```sql
PRIMARY KEY: id (bigint, auto-increment)
FOREIGN KEY: trainee_id → trainees.id, activity_id → activities.id
PURPOSE: Trainee participation tracking
```

**Key Status Values:** present, absent, late, excused

### 5. Asset Management Tables

#### `assets` - Equipment/Resource Tracking
```sql
PRIMARY KEY: id (bigint, auto-increment)
FOREIGN KEY: centre_id → centres.centre_id, category_id, location_id
PURPOSE: Physical asset management
REAL DATA: Assets at Gombak centre are real inventory
```

#### `asset_maintenance` - Maintenance Scheduling
```sql
PRIMARY KEY: id (bigint, auto-increment)
FOREIGN KEY: asset_id → assets.id
PURPOSE: Asset upkeep and maintenance tracking
```

## Data Classification

### 🔴 REAL DATA (Production - Must Preserve)
**Location**: Centre '01' (Gombak)
**Tables Affected**:
- `centres` - Gombak centre record
- `users` - Staff members assigned to centre '01'
- `assets` - Physical inventory at Gombak
- `staff_attendances` - Real staff attendance records

**Critical Preservation Requirements**:
- All user accounts and credentials
- Asset inventory and maintenance history
- Actual attendance records
- Centre configuration and policies

### 🟡 MIXED DATA (Real + Seeded)
**Tables Affected**:
- `trainees` - Some real clients, some test data
- `activities` - Mix of real programs and test activities
- `activity_sessions` - Mix of actual and demo sessions

### 🟢 SEEDED DATA (Test/Demo - Safe to Regenerate)
**Location**: Centres '02'-'05'
**Tables Affected**:
- All non-Gombak centre data
- Demo trainees and activities
- Simulated attendance records
- Test assets and maintenance

**Regeneration Safe**: These can be completely recreated

## Relationships & Dependencies

### Primary Dependency Chain
```
centres (foundation)
├── users (staff)
├── trainees (clients)
├── assets (inventory)
└── activities
    ├── activity_sessions
    ├── activity_enrollments
    └── trainee_attendances
```

### Critical Foreign Key Constraints
1. **Centre-based isolation**: Most tables reference `centre_id`
2. **User relationships**: Activities/attendance link to `users.id`
3. **Trainee relationships**: Enrollments/attendance link to `trainees.id`
4. **Cascading deletes**: Configured for data integrity

### Security Model
- **Multi-tenant**: Centre-based data isolation
- **Role-based**: User roles determine access levels
- **Centre-scoped**: Users can only access their centre's data

## Migration Strategy Recommendations

### Phase 1: Data Backup & Analysis
```bash
# 1. Full database backup
mysqldump -u root -p creams_db > backup_before_migration.sql

# 2. Export real data specifically
mysqldump -u root -p creams_db \
  --where="centre_id='01'" \
  users trainees assets activities > gombak_real_data.sql
```

### Phase 2: Clean Migration Structure
**Recommended approach**:

1. **Create new migration files** with proper sequencing:
   - `001_create_foundation_tables.php` (centres, users)
   - `002_create_client_tables.php` (trainees, categories)
   - `003_create_service_tables.php` (activities, sessions, enrollments)
   - `004_create_attendance_tables.php` (staff/trainee attendance)
   - `005_create_asset_tables.php` (assets, maintenance, locations)
   - `006_create_communication_tables.php` (messages, notifications)
   - `007_add_foreign_keys.php` (all relationships)

2. **Preserve real data seeders**:
   - `RealDataSeeder.php` - Only Gombak centre data
   - `DemoDataSeeder.php` - All test centres

### Phase 3: Data Type Standardization
**Issues to fix**:
- Centre ID types: Ensure consistent `varchar(10)` vs `int`
- Attendance status enums: Standardize across tables
- Timestamp handling: Consistent `timestamp` vs `datetime`

### Phase 4: Missing Migrations
**Tables without proper migrations**:
- `trainee_attendances` - Create dedicated migration
- Various indexes - Ensure all are properly defined

## Real vs Seeded Data Analysis

### Real Data Patterns (Centre '01')
**Staff Data Characteristics**:
- IIUM email addresses (@iium.edu.my)
- Real Malaysian names and phone numbers
- Actual job titles and qualifications
- Historical attendance records

**Asset Data Characteristics**:
- Real equipment serial numbers
- Actual purchase dates and costs
- Genuine maintenance schedules
- Physical location references

### Seeded Data Patterns (Centres '02'-'05')
**Generated Data Characteristics**:
- Faker library generated names/emails
- Randomized but realistic Malaysian data
- Consistent date ranges (July-August 2024)
- Statistical distributions (attendance rates, etc.)

## Current Schema Inconsistencies

### Missing Migrations
1. `trainee_attendances` table has no migration file
2. Some foreign key constraints missing in migrations
3. Index definitions incomplete

### Data Type Mismatches
1. Centre ID handling: String vs Integer comparisons
2. Attendance status enums: Slight variations between tables
3. Timestamp precision: Some datetime vs timestamp inconsistencies

### Naming Inconsistencies
1. Some columns use different prefixes
2. Boolean field naming not fully consistent
3. Foreign key naming patterns vary slightly

## Recommendations for Revamp

### 1. Migration File Restructure
- Create logical, sequenced migration files
- Ensure all tables have proper migrations
- Add comprehensive foreign key definitions

### 2. Data Preservation Strategy
```php
// Backup real data
$realCentreData = DB::table('users')->where('centre_id', '01')->get();
$realAssetData = DB::table('assets')->where('centre_id', '01')->get();

// Store in JSON for restoration
file_put_contents('real_data_backup.json', json_encode([
    'users' => $realCentreData,
    'assets' => $realAssetData,
    // ... other real data
]));
```

### 3. Seeder Reorganization
- `RealDataSeeder` - Only actual production data
- `TestDataSeeder` - Demo centres and sample data
- `BaseDataSeeder` - Required reference data (categories, etc.)

### 4. Schema Validation
```php
// Add schema validation tests
public function testSchemaConsistency()
{
    // Verify all foreign keys exist
    // Check data type consistency  
    // Validate enum values match
}
```

This guide provides the foundation for safely revamping your migrations while preserving all real production data from the Gombak centre.