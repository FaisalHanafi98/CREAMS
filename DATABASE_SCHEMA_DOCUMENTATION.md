# CREAMS Database Schema Documentation

**Generated:** 2026-02-06
**Database:** MySQL 8.0+
**Total Tables:** 37 (31 core migrations + 6 Laravel framework)
**Character Set:** utf8mb4 (full Unicode support)
**Storage Engine:** InnoDB (all tables)

---

## Table of Contents

1. [Foundation Management Layer](#1-foundation-management-layer)
2. [Client Management Layer](#2-client-management-layer)
3. [Service Delivery Layer](#3-service-delivery-layer)
4. [Attendance Management Layer](#4-attendance-management-layer)
5. [Asset Management Layer](#5-asset-management-layer)
6. [Communication Management Layer](#6-communication-management-layer)
7. [Education Planning Layer](#7-education-planning-layer)
8. [Progress Reporting Layer](#8-progress-reporting-layer)
9. [Auxiliary/System Tables](#9-auxiliarysystem-tables)
10. [Entity Relationship Diagram](#10-entity-relationship-diagram)
11. [Many-to-Many Relationships](#11-many-to-many-relationships)
12. [Cascade Behaviors](#12-cascade-constraint-behaviors)
13. [Indexing Strategy](#13-key-indexing-strategy)
14. [Schema Issues & Recommendations](#14-schema-issues--recommendations)
15. [Summary Statistics](#15-summary-statistics)

---

## 1. Foundation Management Layer

### `centres` Table
**Purpose:** Core entity representing PPDK rehabilitation centres

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| centre_id | VARCHAR(10) | PK | Business key, not auto-increment |
| centre_name | VARCHAR(255) | UNIQUE, NOT NULL | |
| centre_address | TEXT | nullable | |
| centre_phone | VARCHAR(20) | NOT NULL | Malaysian format |
| centre_email | VARCHAR(255) | UNIQUE, NOT NULL | |
| centre_capacity | VARCHAR(10) | nullable | |
| centre_manager | VARCHAR(255) | nullable | |
| centre_manager_contact | VARCHAR(20) | nullable | |
| centre_status | ENUM | NOT NULL | 'active', 'inactive', 'maintenance' |
| centre_description | TEXT | nullable | |
| centre_facilities | JSON | nullable | Equipment/facilities list |
| opening_time | TIME | default: '08:00:00' | |
| is_active | BOOLEAN | default: true | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `centre_id`
- INDEX: `centre_status`
- INDEX: `is_active`
- UNIQUE: `centre_name`
- UNIQUE: `centre_email`

**Relationships:**
- **Has Many:** staffs, trainees, volunteers, activities, assets, letters

---

### `staffs` Table
**Purpose:** User accounts for staff members (Admin, Supervisor, Teacher, AJK)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| iium_id | VARCHAR(50) | UNIQUE, nullable | International Islamic University ID |
| name | VARCHAR(255) | NOT NULL | |
| email | VARCHAR(255) | UNIQUE, NOT NULL | |
| email_verified_at | TIMESTAMP | nullable | |
| password | VARCHAR(255) | NOT NULL | Hashed (bcrypt) |
| phone | VARCHAR(20) | nullable | Malaysian format |
| address | TEXT | nullable | |
| education_level | VARCHAR(100) | nullable | |
| education_specialization | VARCHAR(255) | nullable | |
| teaching_specialization | VARCHAR(255) | nullable | |
| date_of_birth | DATE | nullable | |
| role | ENUM | NOT NULL, default: 'teacher' | 'admin', 'supervisor', 'teacher', 'ajk' |
| status | ENUM | default: 'pending' | 'active', 'inactive', 'pending' |
| centre_id | VARCHAR(10) | FK, nullable | → centres(centre_id) |
| encrypted_id | VARCHAR(255) | nullable | For URL obfuscation |
| avatar | VARCHAR(255) | nullable | Profile picture path |
| position | VARCHAR(100) | nullable | |
| about | TEXT | nullable | |
| centre_location | VARCHAR(255) | nullable | |
| last_accessed_at | TIMESTAMP | nullable | Session tracking |
| remember_token | VARCHAR(100) | nullable | Laravel auth |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(centre_id, role, status)` - Critical for staff filtering queries
- INDEX: `deleted_at`
- UNIQUE: `email`
- UNIQUE: `iium_id`

**Foreign Keys:**
- `centre_id` → centres(centre_id) [RESTRICT ON DELETE]

**Relationships:**
- **Belongs To:** centres
- **Has Many:** activities (as instructor), messages (as sender), attendance records, letters

---

### `sessions` Table
**Purpose:** Laravel session storage (database driver)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | VARCHAR(255) | PK | Session ID |
| user_id | BIGINT | nullable | Foreign key to staffs |
| ip_address | VARCHAR(45) | nullable | Supports IPv6 |
| user_agent | TEXT | nullable | |
| payload | LONGTEXT | NOT NULL | Serialized session data |
| last_activity | INTEGER | NOT NULL | Unix timestamp |

**Indexes:**
- PRIMARY KEY: `id`
- INDEX: `user_id`
- INDEX: `last_activity`

**Security Notes:**
- Contains encrypted session data
- GC (garbage collection) based on `last_activity`
- Should be regularly purged (Laravel scheduler)

---

## 2. Client Management Layer

### `trainees` Table
**Purpose:** Persons with disabilities enrolled in rehabilitation programs

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| trainee_id | VARCHAR(50) | UNIQUE, NOT NULL | Format: TRN{YYYY}{CENTRE_ID}{SEQ} |
| trainee_first_name | VARCHAR(100) | NOT NULL | |
| trainee_last_name | VARCHAR(100) | NOT NULL | |
| trainee_email | VARCHAR(255) | UNIQUE, NOT NULL | |
| ic_number | VARCHAR(15) | UNIQUE, NOT NULL | Malaysian IC/MyKad number |
| trainee_date_of_birth | DATE | NOT NULL | |
| gender | ENUM | NOT NULL | 'Male', 'Female' |
| trainee_phone_number | VARCHAR(20) | nullable | Malaysian format |
| trainee_address | TEXT | nullable | |
| trainee_condition | VARCHAR(255) | nullable | Disability type/diagnosis |
| centre_id | VARCHAR(10) | FK, nullable | → centres(centre_id) |
| centre_name | VARCHAR(255) | nullable | Denormalized for reporting |
| status | ENUM | default: 'active' | 'active', 'inactive', 'graduated' |
| guardian_name | VARCHAR(255) | nullable | |
| guardian_phone | VARCHAR(20) | nullable | |
| guardian_email | VARCHAR(255) | nullable | |
| guardian_relationship | VARCHAR(50) | NOT NULL | In English |
| guardian_address | TEXT | nullable | |
| emergency_contact_name | VARCHAR(255) | nullable | |
| emergency_contact_phone | VARCHAR(20) | nullable | |
| emergency_contact_relationship | VARCHAR(50) | NOT NULL | |
| photo_consent | BOOLEAN | default: false | Three mandatory consents |
| services_consent | BOOLEAN | default: false | |
| data_consent | BOOLEAN | default: false | |
| registration_date | DATE | default: NOW | |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- UNIQUE: `trainee_id`
- UNIQUE: `ic_number` (critical for deduplication)
- UNIQUE: `trainee_email`
- COMPOSITE: `(centre_id, status)` - Dashboard queries
- COMPOSITE: `(status, centre_id)` - Alternate query pattern
- INDEX: `deleted_at`

**Foreign Keys:**
- `centre_id` → centres(centre_id) [RESTRICT ON DELETE]

**Relationships:**
- **Belongs To:** centres
- **Has Many:** activity_enrollments, trainee_attendances, trainee_education_plans, progress_reports
- **Many-to-Many:** activities (via activity_enrollments)

**Data Confidentiality:**
⚠️ **CRITICAL:** Contains PII (IC numbers, addresses, medical conditions). All test data MUST be anonymized.

---

### `volunteers` Table
**Purpose:** Volunteer registration and management

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| volunteer_id | VARCHAR(50) | UNIQUE, NOT NULL | |
| name | VARCHAR(255) | NOT NULL | |
| email | VARCHAR(255) | UNIQUE, NOT NULL | |
| phone | VARCHAR(20) | NOT NULL | |
| address | TEXT | nullable | |
| date_of_birth | DATE | nullable | |
| gender | ENUM | nullable | 'Male', 'Female' |
| occupation | VARCHAR(255) | nullable | |
| skills | VARCHAR(255) | nullable | CSV or JSON |
| availability | TEXT | nullable | |
| centre_id | VARCHAR(10) | FK, nullable | → centres(centre_id) |
| status | ENUM | default: 'applied' | 'applied', 'reviewed', 'approved', 'rejected', 'active', 'inactive' |
| motivation | TEXT | nullable | Why they want to volunteer |
| registration_date | DATE | default: NOW | |
| approval_fields | JSON | nullable | Approval workflow metadata |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- UNIQUE: `volunteer_id`
- UNIQUE: `email`
- COMPOSITE: `(centre_id, status)`

**Foreign Keys:**
- `centre_id` → centres(centre_id) [RESTRICT ON DELETE]

---

### `contact_messages` Table
**Purpose:** Public inquiry form submissions

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| name | VARCHAR(255) | NOT NULL | |
| email | VARCHAR(255) | NOT NULL | |
| phone | VARCHAR(20) | nullable | |
| subject | VARCHAR(255) | NOT NULL | |
| message | TEXT | NOT NULL | |
| inquiry_type | ENUM | NOT NULL | 'general', 'services', 'volunteer', 'donation', 'other' |
| status | ENUM | default: 'new' | 'new', 'read', 'replied', 'resolved' |
| centre_id | VARCHAR(10) | FK, nullable | → centres(centre_id) |
| replied_by | INTEGER | FK, nullable | → staffs(id) |
| replied_at | TIMESTAMP | nullable | |
| reply_message | TEXT | nullable | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(status, inquiry_type)`

**Foreign Keys:**
- `centre_id` → centres(centre_id) [SET NULL ON DELETE]
- `replied_by` → staffs(id) [SET NULL ON DELETE]

---

## 3. Service Delivery Layer

### `activities` Table
**Purpose:** Rehabilitation programs/classes offered at centres

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| activity_name | VARCHAR(255) | NOT NULL | |
| activity_description | TEXT | nullable | |
| category | VARCHAR(100) | nullable | ENUM STRING: 'Autism Spectrum Support', 'Hearing Impairment', 'Visual Impairment', 'Physical Disabilities', 'Learning Support', 'Speech Therapy' |
| centre_id | VARCHAR(10) | FK, NOT NULL | → centres(centre_id) |
| duration_weeks | INTEGER | default: 12 | Program length |
| sessions_per_week | INTEGER | default: 2 | |
| session_duration_minutes | INTEGER | default: 60 | |
| max_participants | INTEGER | default: 10 | Capacity limit |
| learning_outcomes | TEXT | nullable | Expected outcomes |
| activity_location | VARCHAR(255) | nullable | Room/building |
| instructor_id | BIGINT | FK, nullable | → staffs(id) |
| is_active | BOOLEAN | default: true | |
| times_conducted | INTEGER | default: 0 | Usage tracking |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(category, centre_id, is_active)` - Critical for category filtering
- COMPOSITE: `(is_active, centre_id)` - Dashboard queries
- INDEX: `instructor_id`
- INDEX: `centre_id`
- INDEX: `is_active`
- INDEX: `deleted_at`

**Foreign Keys:**
- `centre_id` → centres(centre_id) [RESTRICT ON DELETE]
- `instructor_id` → staffs(id) [SET NULL ON DELETE]

**Relationships:**
- **Belongs To:** centres, staffs (instructor)
- **Has Many:** activity_occurrences, activity_enrollments, iep_activity_goals
- **Many-to-Many:** trainees (via activity_enrollments)

---

### `activity_occurrences` Table
**Purpose:** Individual session instances of activities (formerly activity_sessions)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| activity_id | BIGINT | FK, NOT NULL | → activities(id) |
| session_name | VARCHAR(255) | NOT NULL | e.g., "Week 1 - Session 1" |
| session_description | TEXT | nullable | |
| session_date | DATE | NOT NULL | |
| start_time | TIME | NOT NULL | |
| end_time | TIME | NOT NULL | |
| location | VARCHAR(255) | nullable | |
| instructor_id | BIGINT | FK, nullable | → staffs(id) |
| session_status | ENUM | default: 'scheduled' | 'scheduled', 'ongoing', 'completed', 'cancelled' |
| session_notes | TEXT | nullable | Post-session notes |
| max_participants | INTEGER | nullable | Can override activity default |
| current_participants | INTEGER | nullable | Real-time count |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(activity_id, session_date, session_status)` - Critical for schedule views
- COMPOSITE: `(instructor_id, session_date)` - Instructor schedule
- COMPOSITE: `(activity_id, session_date)` - Activity timeline
- INDEX: `session_date`
- INDEX: `deleted_at`

**Foreign Keys:**
- `activity_id` → activities(id) [CASCADE DELETE]
- `instructor_id` → staffs(id) [SET NULL ON DELETE]

**Relationships:**
- **Belongs To:** activities, staffs (instructor)
- **Has Many:** session_attendance

---

### `activity_enrollments` Table
**Purpose:** Trainee enrollment in activities (many-to-many pivot with extras)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| activity_id | BIGINT | FK, NOT NULL | → activities(id) |
| trainee_id | BIGINT | FK, NOT NULL | → trainees(id) |
| enrollment_date | DATE | NOT NULL | |
| enrollment_status | ENUM | default: 'enrolled' | 'enrolled', 'completed', 'dropped', 'pending' |
| enrollment_notes | TEXT | nullable | |
| progress_percentage | DECIMAL(5,2) | default: 0.00 | 0.00-100.00 |
| attendance_count | INTEGER | default: 0 | Cache for performance |
| completion_date | DATE | nullable | |
| completion_notes | TEXT | nullable | |
| enrolled_by | INTEGER | FK, nullable | → staffs(id) |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- UNIQUE: `(activity_id, trainee_id)` - Prevent duplicate enrollments
- COMPOSITE: `(activity_id, trainee_id, enrollment_status)` - Primary query pattern
- INDEX: `enrollment_date`
- INDEX: `deleted_at`

**Foreign Keys:**
- `activity_id` → activities(id) [CASCADE DELETE]
- `trainee_id` → trainees(id) [CASCADE DELETE]

**Relationships:**
- **Belongs To:** activities, trainees, staffs (enrolled_by)

**⚠️ MISSING INDEX:** `enrolled_by` should be indexed for staff reporting queries.

---

### `activity_schedule_templates` Table
**Purpose:** Reusable schedule templates for activities (NEW - 2026-02-01)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| template_name | VARCHAR(255) | NOT NULL | |
| description | TEXT | nullable | |
| sessions_per_week | INTEGER | NOT NULL | |
| duration_weeks | INTEGER | NOT NULL | |
| session_length_minutes | INTEGER | NOT NULL | |
| days_of_week | JSON | NOT NULL | e.g., ["Monday", "Wednesday", "Friday"] |
| time_slots | JSON | NOT NULL | e.g., [{"start": "09:00", "end": "11:00"}] |
| template_type | ENUM | NOT NULL | 'weekly', 'intensive', 'flexible', 'custom' |
| is_active | BOOLEAN | default: true | |
| created_by | BIGINT | FK, nullable | → staffs(id) |
| centre_id | VARCHAR(10) | FK, nullable | → centres(centre_id) |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`

**Foreign Keys:**
- `created_by` → staffs(id) [SET NULL ON DELETE]
- `centre_id` → centres(centre_id) [SET NULL ON DELETE]

---

### `activity_template_applications` Table
**Purpose:** Track which templates are applied to which activities (NEW - 2026-02-01)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| activity_id | BIGINT | FK, NOT NULL | → activities(id) |
| template_id | BIGINT | FK, NOT NULL | → activity_schedule_templates(id) |
| start_date | DATE | NOT NULL | When this template schedule begins |
| end_date | DATE | NOT NULL | When it ends |
| customizations | JSON | nullable | Overrides to template defaults |
| status | ENUM | default: 'active' | 'active', 'completed', 'cancelled' |
| sessions_generated | INTEGER | default: 0 | Count of activity_occurrences created |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`

**Foreign Keys:**
- `activity_id` → activities(id) [CASCADE DELETE]
- `template_id` → activity_schedule_templates(id) [CASCADE DELETE]

---

## 4. Attendance Management Layer

### `staff_attendances` Table
**Purpose:** Daily staff attendance tracking

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| user_id | INTEGER | FK, NOT NULL | → staffs(id) |
| centre_id | VARCHAR(10) | FK, nullable | → centres(centre_id) |
| attendance_date | DATE | NOT NULL | |
| check_in_time | TIME | nullable | |
| status | ENUM | default: 'absent' | 'present', 'absent', 'late', 'leave' |
| leave_type | VARCHAR(50) | nullable | e.g., "Medical", "Annual", "Emergency" |
| approved | BOOLEAN | default: false | Workflow flag |
| approved_by | INTEGER | FK, nullable | → staffs(id) |
| approved_at | TIMESTAMP | nullable | |
| marked_by_user_id | INTEGER | FK, nullable | → staffs(id) |
| marked_by_email | VARCHAR(255) | nullable | Audit trail |
| remarks | TEXT | nullable | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(user_id, attendance_date)` - Individual attendance history
- COMPOSITE: `(centre_id, attendance_date, status)` - Centre daily reports

**Foreign Keys:**
- `user_id` → staffs(id) [CASCADE DELETE]
- `centre_id` → centres(centre_id) [SET NULL ON DELETE]
- `marked_by_user_id` → staffs(id) [SET NULL ON DELETE]
- `approved_by` → staffs(id) [SET NULL ON DELETE]

---

### `trainee_attendances` Table
**Purpose:** Daily trainee attendance tracking (general, not session-specific)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| trainee_id | BIGINT | FK, NOT NULL | → trainees(id) |
| activity_id | BIGINT | FK, nullable | → activities(id) |
| session_id | BIGINT | FK, nullable | → activity_occurrences(id) |
| attendance_date | DATE | NOT NULL | |
| status | ENUM | default: 'absent' | 'present', 'absent', 'late', 'excused' |
| notes | TEXT | nullable | |
| marked_by_user_id | BIGINT | FK, nullable | → staffs(id) |
| marked_at | TIMESTAMP | nullable | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(trainee_id, attendance_date)`
- COMPOSITE: `(activity_id, session_id)`
- COMPOSITE: `(attendance_date, status)`

**Foreign Keys:**
- `trainee_id` → trainees(id) [CASCADE DELETE]
- `activity_id` → activities(id) [SET NULL ON DELETE]
- `session_id` → activity_occurrences(id) [SET NULL ON DELETE]
- `marked_by_user_id` → staffs(id) [SET NULL ON DELETE]

---

### `session_attendance` Table
**Purpose:** Session-specific attendance (more granular than trainee_attendances)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| session_id | BIGINT | FK, NOT NULL | → activity_occurrences(id) |
| trainee_id | BIGINT | FK, NOT NULL | → trainees(id) |
| attendance_status | ENUM | default: 'present' | 'present', 'absent', 'late', 'excused' |
| check_in_time | TIME | nullable | Actual arrival time |
| check_out_time | TIME | nullable | Actual departure time |
| notes | TEXT | nullable | Session-specific notes |
| marked_by | BIGINT | FK, nullable | → staffs(id) |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(session_id, trainee_id)` - Critical for session attendance queries
- INDEX: `attendance_status`

**Foreign Keys:**
- `session_id` → activity_occurrences(id) [CASCADE DELETE]
- `trainee_id` → trainees(id) [CASCADE DELETE]
- `marked_by` → staffs(id) [SET NULL ON DELETE]

**Design Note:**
Two attendance systems exist:
1. `trainee_attendances` - Daily/general attendance
2. `session_attendance` - Session-specific attendance with check-in/out times

---

### `attendance_alerts` Table
**Purpose:** Automated alerts for low attendance patterns

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| alert_type | ENUM | NOT NULL | 'staff', 'trainee' |
| user_id | INTEGER | FK, nullable | → staffs(id) |
| trainee_id | INTEGER | FK, nullable | → trainees(id) |
| alert_message | VARCHAR(255) | NOT NULL | |
| severity | ENUM | default: 'medium' | 'low', 'medium', 'high', 'critical' |
| is_read | BOOLEAN | default: false | |
| is_resolved | BOOLEAN | default: false | |
| resolved_by | INTEGER | FK, nullable | → staffs(id) |
| resolved_at | TIMESTAMP | nullable | |
| resolution_notes | TEXT | nullable | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(alert_type, severity, is_read)` - Dashboard alert filtering

**Foreign Keys:**
- `user_id` → staffs(id) [CASCADE DELETE]
- `trainee_id` → trainees(id) [CASCADE DELETE]
- `resolved_by` → staffs(id) [SET NULL ON DELETE]

---

## 5. Asset Management Layer

### `asset_categories` Table
**Purpose:** High-level asset categorization

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| category_name | VARCHAR(255) | NOT NULL | e.g., "Medical Equipment", "Furniture" |
| category_description | TEXT | nullable | |
| is_active | BOOLEAN | default: true | |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- INDEX: `is_active`

---

### `asset_parents` Table
**Purpose:** Equipment types/models (template for assets)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| centre_id | VARCHAR(10) | FK, NOT NULL | → centres(centre_id) |
| name | VARCHAR(255) | NOT NULL | e.g., "Wheelchair - Standard" |
| type_description | TEXT | nullable | |
| category_id | BIGINT | FK, NOT NULL | → asset_categories(id) |
| image_path | VARCHAR(255) | nullable | |
| manufacturer | VARCHAR(255) | nullable | |
| requires_maintenance | BOOLEAN | default: false | |
| default_maintenance_interval_days | INTEGER | nullable | |
| is_active | BOOLEAN | default: true | |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(category_id, is_active)`

**Foreign Keys:**
- `category_id` → asset_categories(id) [CASCADE DELETE]

---

### `asset_locations` Table
**Purpose:** Physical locations within centres

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| location_name | VARCHAR(255) | NOT NULL | e.g., "Therapy Room 1" |
| location_description | TEXT | nullable | |
| centre_id | VARCHAR(10) | FK, nullable | → centres(centre_id) |
| building | VARCHAR(255) | nullable | |
| floor | VARCHAR(255) | nullable | |
| room | VARCHAR(255) | nullable | |
| is_active | BOOLEAN | default: true | |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(centre_id, is_active)`

**Foreign Keys:**
- `centre_id` → centres(centre_id) [CASCADE DELETE]

---

### `assets` Table
**Purpose:** Individual asset instances

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| type_id | BIGINT | FK, nullable | → asset_parents(id) |
| category_id | BIGINT | FK, NOT NULL | → asset_categories(id) |
| centre_id | VARCHAR(10) | FK, NOT NULL | → centres(centre_id) |
| location_id | BIGINT | FK, nullable | → asset_locations(id) |
| asset_tag | VARCHAR(255) | UNIQUE, NOT NULL | QR/barcode identifier |
| asset_name | VARCHAR(255) | NOT NULL | |
| asset_description | TEXT | nullable | |
| serial_number | VARCHAR(255) | nullable | |
| model_number | VARCHAR(255) | nullable | |
| manufacturer | VARCHAR(255) | nullable | |
| purchase_date | DATE | nullable | |
| purchase_price | DECIMAL(10,2) | nullable | |
| warranty_expiry | DATE | nullable | |
| condition | ENUM | default: 'good' | 'excellent', 'good', 'fair', 'poor', 'damaged', 'disposed' |
| status | ENUM | default: 'available' | 'available', 'in_use', 'maintenance', 'retired', 'missing' |
| assigned_to_user | INTEGER | FK, nullable | → staffs(id) |
| notes | TEXT | nullable | |
| images | JSON | nullable | Array of image paths |
| is_active | BOOLEAN | default: true | |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- UNIQUE: `asset_tag` (critical for asset tracking)
- COMPOSITE: `(category_id, centre_id, status)` - Inventory queries
- COMPOSITE: `(location_id, condition)` - Location inventory

**Foreign Keys:**
- `type_id` → asset_parents(id) [SET NULL ON DELETE]
- `category_id` → asset_categories(id) [CASCADE DELETE]
- `centre_id` → centres(centre_id) [CASCADE DELETE]
- `location_id` → asset_locations(id) [SET NULL ON DELETE]
- `assigned_to_user` → staffs(id) [SET NULL ON DELETE]

---

### `asset_maintenance` Table
**Purpose:** Scheduled and completed maintenance records

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| asset_id | INTEGER | FK, NOT NULL | → assets(id) |
| maintenance_type | VARCHAR(50) | NOT NULL | e.g., "Preventive", "Corrective" |
| scheduled_date | DATE | NOT NULL | |
| completed_date | DATE | nullable | |
| status | ENUM | default: 'scheduled' | 'scheduled', 'in_progress', 'completed', 'cancelled' |
| priority | ENUM | default: 'normal' | 'low', 'normal', 'high', 'critical' |
| description | TEXT | nullable | |
| cost | DECIMAL(8,2) | nullable | |
| performed_by | VARCHAR(255) | nullable | External vendor or staff name |
| notes | TEXT | nullable | |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(asset_id, scheduled_date, status)` - Maintenance schedule
- COMPOSITE: `(maintenance_type, priority)` - Maintenance dashboard

**Foreign Keys:**
- `asset_id` → assets(id) [CASCADE DELETE]

---

### `asset_maintenance_history` Table
**Purpose:** Historical log of all maintenance performed

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| asset_id | INTEGER | FK, NOT NULL | → assets(id) |
| maintenance_id | INTEGER | FK, nullable | → asset_maintenance(id) |
| maintenance_date | DATE | NOT NULL | |
| maintenance_type | VARCHAR(50) | NOT NULL | |
| description | TEXT | NOT NULL | |
| cost | DECIMAL(8,2) | nullable | |
| performed_by | VARCHAR(255) | NOT NULL | |
| notes | TEXT | nullable | |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(asset_id, maintenance_date)` - Asset history timeline
- COMPOSITE: `(maintenance_id, maintenance_type)` - Link to scheduled maintenance

**Foreign Keys:**
- `asset_id` → assets(id) [CASCADE DELETE]
- `maintenance_id` → asset_maintenance(id) [SET NULL ON DELETE]

---

### `asset_movements` Table
**Purpose:** Asset location change tracking

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| asset_id | INTEGER | FK, NOT NULL | → assets(id) |
| from_location_id | INTEGER | FK, nullable | → asset_locations(id) |
| to_location_id | INTEGER | FK, NOT NULL | → asset_locations(id) |
| moved_by_user_id | INTEGER | FK, NOT NULL | → staffs(id) |
| movement_date | DATETIME | NOT NULL | |
| reason | VARCHAR(255) | nullable | |
| notes | TEXT | nullable | |
| deleted_at | TIMESTAMP | nullable | **SOFT DELETE** |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(asset_id, movement_date)` - Asset movement history
- COMPOSITE: `(from_location_id, to_location_id)` - Location transfer patterns

**Foreign Keys:**
- `asset_id` → assets(id) [CASCADE DELETE]
- `from_location_id` → asset_locations(id) [SET NULL ON DELETE]
- `to_location_id` → asset_locations(id) [CASCADE DELETE]
- `moved_by_user_id` → staffs(id) [CASCADE DELETE]

---

## 6. Communication Management Layer

### `messages` Table
**Purpose:** Internal messaging system

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| sender_id | INTEGER | FK, NOT NULL | → staffs(id) |
| subject | VARCHAR(255) | NOT NULL | |
| message_body | TEXT | NOT NULL | |
| priority | ENUM | default: 'normal' | 'low', 'normal', 'high', 'urgent' |
| status | ENUM | default: 'draft' | 'draft', 'sent', 'read', 'archived' |
| sent_at | TIMESTAMP | nullable | |
| attachment_path | VARCHAR(255) | nullable | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(sender_id, status, sent_at)` - Sender message management
- INDEX: `priority`

**Foreign Keys:**
- `sender_id` → staffs(id) [CASCADE DELETE]

**Relationships:**
- **Has Many:** message_recipients

---

### `message_recipients` Table
**Purpose:** Message recipient tracking (supports 1:N messaging)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| message_id | BIGINT | FK, NOT NULL | → messages(id) |
| recipient_id | INTEGER | FK, NOT NULL | → staffs(id) |
| recipient_type | ENUM | default: 'user' | 'user', 'group', 'centre' |
| is_read | BOOLEAN | default: false | |
| read_at | TIMESTAMP | nullable | |
| is_deleted | BOOLEAN | default: false | Soft delete for recipient |
| deleted_at | TIMESTAMP | nullable | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(message_id, recipient_id, is_read)` - Recipient inbox queries

**Foreign Keys:**
- `message_id` → messages(id) [CASCADE DELETE]
- `recipient_id` → staffs(id) [CASCADE DELETE]

---

### `notifications` Table
**Purpose:** Laravel notification system (polymorphic)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| type | VARCHAR(255) | NOT NULL | Notification class name |
| notifiable_type | VARCHAR(255) | NOT NULL | Polymorphic: typically "App\\Models\\Staff" |
| notifiable_id | BIGINT | NOT NULL | Polymorphic: staff ID |
| data | TEXT | NOT NULL | ⚠️ Should be JSON, currently TEXT |
| read_at | TIMESTAMP | nullable | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(notifiable_type, notifiable_id)` - Polymorphic relationship
- COMPOSITE: `(notifiable_id, read_at)` - Unread notifications query

**⚠️ SCHEMA ISSUE:** `data` column should be JSON type, not TEXT.

---

### `letters` Table
**Purpose:** Formal letters/documents generated from templates

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| letter_id | VARCHAR(255) | UNIQUE, NOT NULL | Business key |
| letter_name | VARCHAR(255) | nullable | |
| letter_date | DATETIME | nullable | |
| letter_title | VARCHAR(255) | nullable | |
| letter_subject | VARCHAR(255) | nullable | |
| letter_content | TEXT | nullable | |
| letter_type | VARCHAR(255) | NOT NULL | e.g., "invitation", "report", "certificate" |
| recipient_id | BIGINT | default: 0 | Polymorphic ID |
| recipient_name | VARCHAR(255) | nullable | |
| recipient_email | VARCHAR(255) | nullable | |
| recipient_address | VARCHAR(255) | nullable | |
| recipient_type | VARCHAR(100) | nullable | e.g., "trainee", "guardian", "staff" |
| subject | VARCHAR(255) | nullable | **LEGACY** - Use letter_subject |
| content | TEXT | nullable | **LEGACY** - Use letter_content |
| date_created | DATE | nullable | **LEGACY** - Use letter_date |
| date_sent | DATE | nullable | **LEGACY** - Use sent_at |
| template_id | BIGINT | FK, nullable | → letter_templates(id) |
| letter_status | VARCHAR(50) | default: 'draft' | **DEPRECATED** - Use status |
| status | ENUM | default: 'draft' | 'draft', 'sent', 'delivered', 'responded' |
| is_sent | BOOLEAN | default: false | |
| sent_at | TIMESTAMP | nullable | |
| pdf_path | VARCHAR(500) | nullable | |
| pdf_file_size | BIGINT UNSIGNED | nullable | |
| letter_file_path | VARCHAR(500) | nullable | |
| pdf_filename | VARCHAR(255) | nullable | |
| file_size_bytes | BIGINT UNSIGNED | nullable | |
| generated_file_type | VARCHAR(50) | nullable | e.g., "PDF", "DOCX" |
| generated_at | TIMESTAMP | nullable | |
| letter_data | JSON | nullable | Template merge data |
| generation_metadata | JSON | nullable | DomPDF metadata |
| notes | TEXT | nullable | |
| created_by | BIGINT | FK, NOT NULL | → staffs(id) |
| generated_by | BIGINT | FK, nullable | → staffs(id) |
| centre_id | VARCHAR(10) | FK, NOT NULL | → centres(centre_id) |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- UNIQUE: `letter_id`
- COMPOSITE: `(letter_type, status, centre_id)` - Letter filtering
- COMPOSITE: `(centre_id, letter_status)` - Legacy query support
- INDEX: `date_created`

**Foreign Keys:**
- `created_by` → staffs(id) [CASCADE DELETE]
- `generated_by` → staffs(id) [SET NULL ON DELETE]
- `template_id` → letter_templates(id) [SET NULL ON DELETE]
- `centre_id` → centres(centre_id) [CASCADE DELETE]

**⚠️ SCHEMA ISSUE:** Duplicate columns (subject/letter_subject, content/letter_content, letter_status/status) indicate incomplete migration.

---

### `letter_templates` Table
**Purpose:** Reusable letter templates

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| template_name | VARCHAR(255) | NOT NULL | |
| template_description | TEXT | nullable | |
| template_type | VARCHAR(255) | NOT NULL | e.g., "invitation", "certificate" |
| template_content | TEXT | NOT NULL | HTML/Blade content with placeholders |
| template_variables | JSON | nullable | Available merge fields |
| header_image_path | VARCHAR(255) | nullable | |
| footer_image_path | VARCHAR(255) | nullable | |
| header_text | TEXT | nullable | |
| footer_text | TEXT | nullable | |
| centre_id | VARCHAR(10) | FK, nullable | → centres(centre_id) |
| usage_count | INTEGER | default: 0 | Cache for analytics |
| last_used_at | TIMESTAMP | nullable | |
| required_fields | JSON | nullable | Validation rules |
| is_active | BOOLEAN | default: true | |
| created_by | INTEGER | FK, NOT NULL | → staffs(id) |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`
- COMPOSITE: `(template_type, is_active)`
- COMPOSITE: `(centre_id, is_active)`

**Foreign Keys:**
- `created_by` → staffs(id) [CASCADE DELETE]
- `centre_id` → centres(centre_id) [SET NULL ON DELETE]

**⚠️ MISSING INDEX:** Should add index on `usage_count` for analytics queries.

---

## 7. Education Planning Layer

### `trainee_education_plans` Table
**Purpose:** Individualized Education Plans (IEP) for trainees

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| trainee_id | BIGINT | FK, NOT NULL | → trainees(id) |
| plan_name | VARCHAR(255) | NOT NULL | |
| plan_description | TEXT | nullable | |
| start_date | DATE | NOT NULL | |
| end_date | DATE | NOT NULL | |
| review_date | DATE | nullable | Next review scheduled |
| overall_goals | JSON | nullable | High-level objectives |
| strengths | JSON | nullable | Trainee strengths assessment |
| challenges | JSON | nullable | Areas needing support |
| support_services | JSON | nullable | Required services |
| status | VARCHAR(20) | default: 'Active' | 'Active', 'Completed', 'Suspended', 'Under Review' |
| plan_type | VARCHAR(20) | default: 'Annual' | 'Annual', 'Quarterly', 'Custom' |
| target_completion_percentage | DECIMAL(5,2) | default: 0 | Expected progress |
| notes | TEXT | nullable | |
| created_by | BIGINT | FK, nullable | → staffs(id) |
| last_updated_by | BIGINT | FK, nullable | → staffs(id) |
| last_reviewed_at | TIMESTAMP | nullable | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`

**Foreign Keys:**
- `trainee_id` → trainees(id) [CASCADE DELETE]
- `created_by` → staffs(id) [SET NULL ON DELETE]
- `last_updated_by` → staffs(id) [SET NULL ON DELETE]

**Relationships:**
- **Belongs To:** trainees
- **Has Many:** iep_activity_goals, progress_reports

---

### `iep_activity_goals` Table
**Purpose:** Specific goals within IEPs linked to activities

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| iep_id | BIGINT | FK, NOT NULL | → trainee_education_plans(id) |
| activity_id | BIGINT | FK, nullable | → activities(id) |
| learning_outcome_id | BIGINT | FK, nullable | → learning_outcomes(id) [MISSING TABLE] |
| goal_title | VARCHAR(255) | NOT NULL | |
| goal_description | TEXT | nullable | |
| target_start_date | DATE | nullable | |
| target_completion_date | DATE | nullable | |
| progress_tracking_method | VARCHAR(30) | nullable | e.g., "Observation", "Assessment" |
| target_percentage | DECIMAL(5,2) | default: 0 | Expected achievement |
| priority_level | VARCHAR(20) | default: 'Medium' | 'Low', 'Medium', 'High', 'Critical' |
| goal_status | VARCHAR(30) | default: 'Not Started' | 'Not Started', 'In Progress', 'Completed', 'On Hold' |
| success_criteria | JSON | nullable | Measurable outcomes |
| accommodation_strategies | JSON | nullable | Support strategies |
| notes | TEXT | nullable | |
| current_progress_percentage | DECIMAL(5,2) | default: 0 | Actual progress |
| last_progress_update | TIMESTAMP | nullable | |
| assigned_user_id | BIGINT | FK, nullable | → staffs(id) |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`

**Foreign Keys:**
- `iep_id` → trainee_education_plans(id) [CASCADE DELETE]
- `activity_id` → activities(id) [SET NULL ON DELETE]
- `assigned_user_id` → staffs(id) [SET NULL ON DELETE]

**⚠️ SCHEMA ISSUE:** References `learning_outcomes` table which doesn't exist in core migrations.

---

## 8. Progress Reporting Layer

### `progress_reports` Table
**Purpose:** Comprehensive trainee progress reports

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| trainee_id | BIGINT | FK, NOT NULL | → trainees(id) |
| iep_id | BIGINT | FK, nullable | → trainee_education_plans(id) |
| report_title | VARCHAR(255) | NOT NULL | |
| report_type | VARCHAR(255) | NOT NULL | 'Weekly', 'Monthly', 'Quarterly', 'Annual', 'Custom' |
| report_period | VARCHAR(255) | NOT NULL | |
| period_start_date | DATE | NOT NULL | |
| period_end_date | DATE | NOT NULL | |
| activity_progress | JSON | nullable | Per-activity progress data |
| learning_outcomes_progress | JSON | nullable | |
| iep_goals_progress | JSON | nullable | |
| attendance_summary | JSON | nullable | Attendance statistics |
| competency_achievements | JSON | nullable | Skills achieved |
| recommendations | JSON | nullable | Next steps |
| overall_summary | TEXT | nullable | |
| strengths_observed | TEXT | nullable | |
| areas_for_improvement | TEXT | nullable | |
| next_period_goals | TEXT | nullable | |
| status | VARCHAR(255) | NOT NULL | 'Draft', 'In Review', 'Approved', 'Shared' |
| parent_accessible | BOOLEAN | default: false | Guardian portal access |
| shared_with_parents_at | TIMESTAMP | nullable | |
| generated_by | BIGINT | FK, NOT NULL | → staffs(id) |
| reviewed_by | BIGINT | FK, nullable | → staffs(id) |
| approved_by | BIGINT | FK, nullable | → staffs(id) |
| reviewed_at | TIMESTAMP | nullable | |
| approved_at | TIMESTAMP | nullable | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`

**Foreign Keys:**
- `trainee_id` → trainees(id) [CASCADE DELETE]
- `iep_id` → trainee_education_plans(id) [SET NULL ON DELETE]
- `generated_by` → staffs(id)
- `reviewed_by` → staffs(id) [SET NULL ON DELETE]
- `approved_by` → staffs(id) [SET NULL ON DELETE]

**⚠️ MISSING INDEX:** `status` should be indexed for dashboard filtering.

---

## 9. Auxiliary/System Tables

### `audit_logs` Table
**Purpose:** Comprehensive audit trail for all critical actions

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| action | VARCHAR(255) | NOT NULL | e.g., "created", "updated", "deleted" |
| entity_type | VARCHAR(255) | NOT NULL | Model class name |
| entity_id | BIGINT | NOT NULL | Model ID |
| user_id | BIGINT | nullable | Actor (null for system actions) |
| user_role | VARCHAR(255) | nullable | RBAC role at time of action |
| original_values | JSON | nullable | Before state |
| new_values | JSON | nullable | After state |
| ip_address | VARCHAR(255) | nullable | Request IP |
| user_agent | TEXT | nullable | Browser info |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`

**Security Notes:**
- Should have retention policy (e.g., keep 2 years)
- Consider partitioning by date for performance
- Critical for compliance and forensics

---

### `activity_logs` Table
**Purpose:** Laravel activitylog package (spatie/laravel-activitylog)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| activity | VARCHAR(255) | NOT NULL | Activity name |
| description | TEXT | nullable | |
| user_id | BIGINT | nullable | Actor |
| user_role | VARCHAR(255) | nullable | |
| model_type | VARCHAR(255) | nullable | Eloquent model class |
| model_id | BIGINT | nullable | Model ID |
| changes | JSON | nullable | Changed attributes |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`

**Note:** Overlaps with `audit_logs` - consider consolidating.

---

### `public_holidays` Table
**Purpose:** Malaysian public holiday calendar

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| holiday_name | VARCHAR(255) | NOT NULL | |
| holiday_date | DATE | NOT NULL | |
| state | VARCHAR(255) | nullable | Malaysian state code (e.g., "KL", "SGR") |
| is_national | BOOLEAN | NOT NULL | Federal vs state holiday |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Indexes:**
- PRIMARY KEY: `id`

**Usage:** Schedule planning, attendance calculations, report filtering.

---

### `failed_jobs` Table
**Purpose:** Laravel queue failed job tracking

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| uuid | VARCHAR(255) | UNIQUE, NOT NULL | Job UUID |
| connection | TEXT | NOT NULL | Queue connection |
| queue | TEXT | NOT NULL | Queue name |
| payload | LONGTEXT | NOT NULL | Serialized job |
| exception | LONGTEXT | NOT NULL | Stack trace |
| failed_at | TIMESTAMP | NOT NULL | |

**Indexes:**
- PRIMARY KEY: `id`
- UNIQUE: `uuid`

---

## 10. Entity Relationship Diagram

### **Comprehensive ERD (Text Format)**

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         CREAMS DATABASE ARCHITECTURE                         │
└─────────────────────────────────────────────────────────────────────────────┘

FOUNDATION LAYER:
═════════════════
       centres (PK: centre_id) [VARCHAR Business Key]
          ├─[1:N]─ staffs (FK: centre_id) [RESTRICT]
          ├─[1:N]─ trainees (FK: centre_id) [RESTRICT]
          ├─[1:N]─ volunteers (FK: centre_id) [RESTRICT]
          ├─[1:N]─ activities (FK: centre_id) [RESTRICT]
          ├─[1:N]─ assets (FK: centre_id) [CASCADE]
          ├─[1:N]─ asset_locations (FK: centre_id) [CASCADE]
          ├─[1:N]─ letters (FK: centre_id) [CASCADE]
          └─[1:N]─ letter_templates (FK: centre_id) [SET NULL]

STAFF HIERARCHY:
════════════════
       staffs (PK: id) [Soft Delete]
          ├─[1:N]─ activities (FK: instructor_id) [SET NULL]
          ├─[1:N]─ activity_occurrences (FK: instructor_id) [SET NULL]
          ├─[1:N]─ activity_enrollments (FK: enrolled_by)
          ├─[1:N]─ staff_attendances (FK: user_id) [CASCADE]
          ├─[1:N]─ staff_attendances (FK: marked_by_user_id) [SET NULL]
          ├─[1:N]─ staff_attendances (FK: approved_by) [SET NULL]
          ├─[1:N]─ trainee_attendances (FK: marked_by_user_id) [SET NULL]
          ├─[1:N]─ session_attendance (FK: marked_by) [SET NULL]
          ├─[1:N]─ messages (FK: sender_id) [CASCADE]
          ├─[1:N]─ message_recipients (FK: recipient_id) [CASCADE]
          ├─[1:N]─ contact_messages (FK: replied_by) [SET NULL]
          ├─[1:N]─ assets (FK: assigned_to_user) [SET NULL]
          ├─[1:N]─ asset_movements (FK: moved_by_user_id) [CASCADE]
          ├─[1:N]─ letters (FK: created_by) [CASCADE]
          ├─[1:N]─ letters (FK: generated_by) [SET NULL]
          ├─[1:N]─ letter_templates (FK: created_by) [CASCADE]
          ├─[1:N]─ trainee_education_plans (FK: created_by) [SET NULL]
          ├─[1:N]─ trainee_education_plans (FK: last_updated_by) [SET NULL]
          ├─[1:N]─ iep_activity_goals (FK: assigned_user_id) [SET NULL]
          ├─[1:N]─ progress_reports (FK: generated_by)
          ├─[1:N]─ progress_reports (FK: reviewed_by) [SET NULL]
          ├─[1:N]─ progress_reports (FK: approved_by) [SET NULL]
          ├─[1:N]─ attendance_alerts (FK: user_id) [CASCADE]
          └─[1:N]─ attendance_alerts (FK: resolved_by) [SET NULL]

TRAINEE JOURNEY:
════════════════
       trainees (PK: id) [Soft Delete, UNIQUE: ic_number]
          ├─[1:N]─ activity_enrollments (FK: trainee_id) [CASCADE]
          │         └─[M:N]─ activities (via activity_enrollments)
          ├─[1:N]─ trainee_attendances (FK: trainee_id) [CASCADE]
          ├─[1:N]─ session_attendance (FK: trainee_id) [CASCADE]
          ├─[1:N]─ trainee_education_plans (FK: trainee_id) [CASCADE]
          ├─[1:N]─ progress_reports (FK: trainee_id) [CASCADE]
          └─[1:N]─ attendance_alerts (FK: trainee_id) [CASCADE]

SERVICE DELIVERY CORE:
══════════════════════
       activities (PK: id) [Soft Delete]
          ├─[1:N]─ activity_occurrences (FK: activity_id) [CASCADE]
          │         └─[1:N]─ session_attendance (FK: session_id) [CASCADE]
          ├─[1:N]─ activity_enrollments (FK: activity_id) [CASCADE]
          │         └─[UNIQUE: (activity_id, trainee_id)]
          ├─[1:N]─ activity_template_applications (FK: activity_id) [CASCADE]
          ├─[1:N]─ iep_activity_goals (FK: activity_id) [SET NULL]
          └─[1:N]─ trainee_attendances (FK: activity_id) [SET NULL]

       activity_schedule_templates (PK: id)
          └─[1:N]─ activity_template_applications (FK: template_id) [CASCADE]

COMPETENCY & PROGRESS:
══════════════════════
       trainee_education_plans (PK: id)
          ├─[1:N]─ iep_activity_goals (FK: iep_id) [CASCADE]
          └─[1:N]─ progress_reports (FK: iep_id) [SET NULL]

ASSET MANAGEMENT:
═════════════════
       asset_categories (PK: id) [Soft Delete]
          ├─[1:N]─ asset_parents (FK: category_id) [CASCADE]
          └─[1:N]─ assets (FK: category_id) [CASCADE]

       asset_parents (PK: id) [Soft Delete]
          └─[1:N]─ assets (FK: type_id) [SET NULL]

       asset_locations (PK: id) [Soft Delete]
          ├─[1:N]─ assets (FK: location_id) [SET NULL]
          ├─[1:N]─ asset_movements (FK: from_location_id) [SET NULL]
          └─[1:N]─ asset_movements (FK: to_location_id) [CASCADE]

       assets (PK: id) [Soft Delete, UNIQUE: asset_tag]
          ├─[1:N]─ asset_maintenance (FK: asset_id) [CASCADE]
          ├─[1:N]─ asset_maintenance_history (FK: asset_id) [CASCADE]
          └─[1:N]─ asset_movements (FK: asset_id) [CASCADE]

       asset_maintenance (PK: id) [Soft Delete]
          └─[1:N]─ asset_maintenance_history (FK: maintenance_id) [SET NULL]

COMMUNICATION:
══════════════
       messages (PK: id)
          └─[1:N]─ message_recipients (FK: message_id) [CASCADE]

       letter_templates (PK: id)
          └─[1:N]─ letters (FK: template_id) [SET NULL]

       notifications (PK: id) [Polymorphic]
          └─[MORPH]─ notifiable (notifiable_type, notifiable_id)

SYSTEM/AUXILIARY:
═════════════════
       audit_logs (PK: id) - Comprehensive audit trail
       activity_logs (PK: id) - Laravel activitylog package
       public_holidays (PK: id) - Malaysian calendar
       failed_jobs (PK: id, UNIQUE: uuid) - Queue failures
       sessions (PK: id) - Session storage
```

---

## 11. Many-to-Many Relationships

### **Trainees ↔ Activities (via activity_enrollments)**

**Pivot Table:** `activity_enrollments`

**Extra Pivot Columns:**
- enrollment_date (DATE)
- enrollment_status (ENUM: 'enrolled', 'completed', 'dropped', 'pending')
- enrollment_notes (TEXT)
- progress_percentage (DECIMAL 5,2)
- attendance_count (INTEGER) - Cached for performance
- completion_date (DATE, nullable)
- completion_notes (TEXT, nullable)
- enrolled_by (FK → staffs.id)

**Unique Constraint:** `(activity_id, trainee_id)` - Prevents duplicate enrollments

**Eloquent Accessor Methods (in Models):**
```php
// Trainee model
public function activities() {
    return $this->belongsToMany(Activity::class, 'activity_enrollments')
                ->withPivot('enrollment_status', 'progress_percentage', 'attendance_count')
                ->withTimestamps();
}

// Activity model
public function trainees() {
    return $this->belongsToMany(Trainee::class, 'activity_enrollments')
                ->withPivot('enrollment_status', 'progress_percentage', 'attendance_count')
                ->withTimestamps();
}
```

---

## 12. Cascade & Constraint Behaviors

### **CASCADE DELETE** (Deleting parent deletes children automatically)

| Parent Table | Child Table | Rationale |
|--------------|-------------|-----------|
| **centres** | asset_locations | Location meaningless without centre |
| **centres** | assets | Asset ownership tied to centre |
| **centres** | letters | Letters tied to centre operations |
| **staffs** | staff_attendances | Attendance record meaningless without staff |
| **staffs** | messages | Message integrity (sender identity) |
| **staffs** | message_recipients | Recipient identity integrity |
| **staffs** | asset_movements | Audit trail integrity |
| **staffs** | letter_templates | Template ownership |
| **staffs** | letters | Letter authorship |
| **activities** | activity_occurrences | Sessions belong to activity lifecycle |
| **activities** | activity_enrollments | Enrollments belong to activity |
| **activity_occurrences** | session_attendance | Attendance tied to session instance |
| **activity_schedule_templates** | activity_template_applications | Applications tied to template |
| **trainees** | activity_enrollments | Trainee departure cleanup |
| **trainees** | trainee_attendances | Attendance records cleanup |
| **trainees** | session_attendance | Session attendance cleanup |
| **trainees** | trainee_education_plans | IEP belongs to trainee journey |
| **trainees** | progress_reports | Reports belong to trainee |
| **trainees** | attendance_alerts | Alerts tied to trainee status |
| **trainee_education_plans** | iep_activity_goals | Goals belong to IEP |
| **assets** | asset_maintenance | Maintenance records tied to asset |
| **assets** | asset_maintenance_history | History tied to asset |
| **assets** | asset_movements | Movement history tied to asset |
| **asset_categories** | asset_parents | Parent types belong to category |
| **asset_categories** | assets | Assets belong to category |
| **asset_locations** | asset_movements (to_location_id) | Destination integrity |

### **SET NULL ON DELETE** (Deleting parent sets FK to NULL)

| Parent Table | Child Table | Foreign Key Column | Rationale |
|--------------|-------------|-------------------|-----------|
| **centres** | staffs | centre_id | Staff can exist without centre assignment |
| **centres** | trainees | centre_id | Trainee record preserved for historical data |
| **centres** | volunteers | centre_id | Volunteer record preserved |
| **centres** | contact_messages | centre_id | Message preserved for audit |
| **centres** | letter_templates | centre_id | Template can become global |
| **staffs** | activities | instructor_id | Activity continues without instructor |
| **staffs** | activity_occurrences | instructor_id | Session can be reassigned |
| **staffs** | staff_attendances | marked_by_user_id | Audit trail preserved |
| **staffs** | staff_attendances | approved_by | Approval record preserved |
| **staffs** | trainee_attendances | marked_by_user_id | Audit trail preserved |
| **staffs** | session_attendance | marked_by | Audit trail preserved |
| **staffs** | assets | assigned_to_user | Asset returns to pool |
| **staffs** | trainee_education_plans | created_by | Plan preserved |
| **staffs** | trainee_education_plans | last_updated_by | Plan preserved |
| **staffs** | iep_activity_goals | assigned_user_id | Goal can be reassigned |
| **staffs** | progress_reports | reviewed_by | Report preserved |
| **staffs** | progress_reports | approved_by | Report preserved |
| **staffs** | contact_messages | replied_by | Message preserved |
| **activities** | iep_activity_goals | activity_id | Goal can exist without activity reference |
| **activities** | trainee_attendances | activity_id | General attendance preserved |
| **activity_occurrences** | trainee_attendances | session_id | General attendance preserved |
| **trainee_education_plans** | progress_reports | iep_id | Report can exist independently |
| **letter_templates** | letters | template_id | Letter preserved even if template deleted |
| **asset_parents** | assets | type_id | Asset continues with generic type |
| **asset_locations** | assets | location_id | Asset location becomes unspecified |
| **asset_locations** | asset_movements | from_location_id | Movement record preserved |
| **asset_maintenance** | asset_maintenance_history | maintenance_id | History preserved |

### **RESTRICT ON DELETE** (Prevent deletion if children exist)

| Parent Table | Child Table | Foreign Key Column | Rationale |
|--------------|-------------|-------------------|-----------|
| **centres** | staffs | centre_id | Cannot delete centre with active staff |
| **centres** | trainees | centre_id | Cannot delete centre with active trainees |
| **centres** | volunteers | centre_id | Cannot delete centre with volunteers |
| **centres** | activities | centre_id | Cannot delete centre with activities |

---

## 13. Key Indexing Strategy

### **Composite Indexes** (Multi-column, order matters)

| Table | Index Columns | Query Purpose |
|-------|--------------|---------------|
| staffs | (centre_id, role, status) | Staff filtering by centre and role |
| trainees | (centre_id, status) | Active trainees per centre |
| trainees | (status, centre_id) | Status-based queries (alternate order) |
| volunteers | (centre_id, status) | Volunteer applications per centre |
| contact_messages | (status, inquiry_type) | Message queue filtering |
| activities | (category, centre_id, is_active) | **CRITICAL** - Category listing |
| activities | (is_active, centre_id) | Dashboard active activities |
| activity_occurrences | (activity_id, session_date, session_status) | **CRITICAL** - Session timeline |
| activity_occurrences | (instructor_id, session_date) | Instructor schedule view |
| activity_occurrences | (activity_id, session_date) | Activity session history |
| activity_enrollments | (activity_id, trainee_id, enrollment_status) | Enrollment queries |
| staff_attendances | (user_id, attendance_date) | Staff attendance history |
| staff_attendances | (centre_id, attendance_date, status) | Centre daily attendance |
| trainee_attendances | (trainee_id, attendance_date) | Trainee attendance history |
| trainee_attendances | (activity_id, session_id) | Session attendance lookup |
| trainee_attendances | (attendance_date, status) | Date-based attendance reports |
| session_attendance | (session_id, trainee_id) | **CRITICAL** - Session attendance marking |
| attendance_alerts | (alert_type, severity, is_read) | Alert dashboard |
| assets | (category_id, centre_id, status) | Asset inventory queries |
| assets | (location_id, condition) | Location-based inventory |
| asset_maintenance | (asset_id, scheduled_date, status) | Maintenance schedule |
| asset_maintenance | (maintenance_type, priority) | Maintenance dashboard |
| asset_maintenance_history | (asset_id, maintenance_date) | Asset history timeline |
| asset_maintenance_history | (maintenance_id, maintenance_type) | Link to scheduled maintenance |
| asset_movements | (asset_id, movement_date) | Asset movement history |
| asset_movements | (from_location_id, to_location_id) | Location transfer patterns |
| asset_parents | (category_id, is_active) | Asset type listing |
| asset_locations | (centre_id, is_active) | Centre location listing |
| messages | (sender_id, status, sent_at) | Sender outbox queries |
| message_recipients | (message_id, recipient_id, is_read) | Recipient inbox queries |
| notifications | (notifiable_type, notifiable_id) | Polymorphic notifications |
| notifications | (notifiable_id, read_at) | Unread notifications count |
| letters | (letter_type, status, centre_id) | Letter filtering |
| letters | (centre_id, letter_status) | Legacy letter queries |
| letter_templates | (template_type, is_active) | Template selection |
| letter_templates | (centre_id, is_active) | Centre-specific templates |

### **Unique Indexes** (Business constraints)

| Table | Columns | Purpose |
|-------|---------|---------|
| centres | centre_email | Email uniqueness |
| centres | centre_name | Name uniqueness |
| staffs | email | Login uniqueness |
| staffs | iium_id | IIUM student/staff ID uniqueness |
| trainees | trainee_id | Business key (TRN{YYYY}{CENTRE}{SEQ}) |
| trainees | ic_number | **CRITICAL** - Malaysian IC deduplication |
| trainees | trainee_email | Email uniqueness |
| volunteers | volunteer_id | Business key |
| volunteers | email | Email uniqueness |
| assets | asset_tag | **CRITICAL** - QR code/barcode scanning |
| activity_enrollments | (activity_id, trainee_id) | **CRITICAL** - Prevent duplicate enrollments |
| letters | letter_id | Business key |
| failed_jobs | uuid | Job uniqueness |
| sessions | id | Session ID |

### **Single Column Indexes**

| Table | Column | Query Purpose |
|-------|--------|---------------|
| centres | centre_status | Active/inactive filtering |
| centres | is_active | Quick active centre lookup |
| staffs | deleted_at | Soft delete queries |
| trainees | deleted_at | Soft delete queries |
| activities | instructor_id | Instructor workload queries |
| activities | centre_id | Centre activity listing |
| activities | is_active | Active activity filtering |
| activities | deleted_at | Soft delete queries |
| activity_occurrences | session_date | Date-based queries |
| activity_occurrences | deleted_at | Soft delete queries |
| activity_enrollments | enrollment_date | Enrollment timeline |
| activity_enrollments | deleted_at | Soft delete queries |
| session_attendance | attendance_status | Status filtering |
| messages | priority | Priority inbox |
| letters | date_created | Letter timeline |
| asset_categories | is_active | Active category filtering |
| sessions | user_id | User session lookup |
| sessions | last_activity | Garbage collection |

---

## 14. Schema Issues & Recommendations

### **CRITICAL Issues** ⚠️

1. **Polymorphic Column Type Mismatch**
   - **Table:** `notifications`
   - **Issue:** `data` column is TEXT instead of JSON
   - **Impact:** Can't use JSON queries/operators, Laravel casts may fail
   - **Fix:**
     ```sql
     ALTER TABLE notifications MODIFY COLUMN data JSON NOT NULL;
     ```

2. **Missing Foreign Table**
   - **Referenced:** `learning_outcomes` table
   - **Referenced By:** `iep_activity_goals.learning_outcome_id`
   - **Impact:** Foreign key constraint will fail if enforced
   - **Fix:** Either create `learning_outcomes` migration or remove FK column

3. **Legacy Column Duplication**
   - **Table:** `letters`
   - **Issue:** Duplicate columns: `subject`/`letter_subject`, `content`/`letter_content`, `letter_status`/`status`
   - **Impact:** Data inconsistency, confusion, wasted storage
   - **Fix:** Data migration + column removal:
     ```sql
     UPDATE letters SET letter_subject = COALESCE(letter_subject, subject);
     UPDATE letters SET letter_content = COALESCE(letter_content, content);
     UPDATE letters SET status = CASE letter_status
         WHEN 'draft' THEN 'draft'
         WHEN 'sent' THEN 'sent'
         ELSE 'draft'
     END WHERE status = 'draft' AND letter_status IS NOT NULL;
     ALTER TABLE letters DROP COLUMN subject, DROP COLUMN content, DROP COLUMN letter_status;
     ```

### **HIGH Priority Recommendations** 🔧

1. **Missing Performance Indexes**
   - **Table:** `activity_enrollments`
   - **Column:** `enrolled_by` (FK to staffs)
   - **Reason:** Staff workload reporting queries
   - **Fix:**
     ```sql
     ALTER TABLE activity_enrollments ADD INDEX idx_enrolled_by (enrolled_by);
     ```

2. **Missing Performance Indexes**
   - **Table:** `progress_reports`
   - **Column:** `status`
   - **Reason:** Dashboard filtering by report status
   - **Fix:**
     ```sql
     ALTER TABLE progress_reports ADD INDEX idx_status (status);
     ```

3. **Missing Performance Indexes**
   - **Table:** `letter_templates`
   - **Column:** `usage_count`
   - **Reason:** Analytics queries for popular templates
   - **Fix:**
     ```sql
     ALTER TABLE letter_templates ADD INDEX idx_usage_count (usage_count);
     ```

4. **Missing Composite Index**
   - **Table:** `letters`
   - **Columns:** `(centre_id, letter_status)`
   - **Reason:** Already used in queries but `letter_status` is deprecated
   - **Fix:** Migrate to use `status` column then reindex

### **MEDIUM Priority Improvements** 🔨

1. **Audit Log Table Partitioning**
   - **Tables:** `audit_logs`, `activity_logs`
   - **Issue:** Will grow unbounded over time
   - **Recommendation:** Implement date-based partitioning
   - **Example:**
     ```sql
     ALTER TABLE audit_logs PARTITION BY RANGE (YEAR(created_at)) (
         PARTITION p2024 VALUES LESS THAN (2025),
         PARTITION p2025 VALUES LESS THAN (2026),
         PARTITION p2026 VALUES LESS THAN (2027),
         PARTITION pfuture VALUES LESS THAN MAXVALUE
     );
     ```

2. **Session Garbage Collection**
   - **Table:** `sessions`
   - **Issue:** Laravel GC runs per-request (inefficient at scale)
   - **Recommendation:** Use scheduler + batch delete:
     ```php
     // In app/Console/Kernel.php
     $schedule->command('session:gc')->daily();
     ```

3. **Denormalized Fields Maintenance**
   - **Tables with denormalization:**
     - `trainees.centre_name` (denormalizes from centres)
     - `activities.times_conducted` (cache)
     - `activity_enrollments.attendance_count` (cache)
     - `letter_templates.usage_count` (cache)
   - **Recommendation:** Implement observer patterns to keep synced

4. **Consistency: ENUM vs VARCHAR**
   - **Issue:** Some status fields use ENUM, others use VARCHAR
   - **Examples:**
     - `activities.category` - VARCHAR but acts like ENUM
     - `progress_reports.status` - VARCHAR but should be ENUM
   - **Recommendation:** Standardize to VARCHAR for flexibility or ENUM for strictness

### **LOW Priority (Nice-to-Have)** 💡

1. **Full-Text Search Indexes**
   - **Tables:** `activities` (activity_name, activity_description), `trainees` (names)
   - **Reason:** Faster search than LIKE queries
   - **Fix:**
     ```sql
     ALTER TABLE activities ADD FULLTEXT INDEX ft_activity_search (activity_name, activity_description);
     ALTER TABLE trainees ADD FULLTEXT INDEX ft_trainee_name (trainee_first_name, trainee_last_name);
     ```

2. **Database Views for Common Queries**
   - **Example:** Active trainee enrollments with attendance rates
   - **Benefit:** Simplifies complex joins in controllers

3. **Trigger-Based Audit Trail**
   - **Alternative to model observers**
   - **Benefit:** Captures direct SQL updates, more reliable

---

## 15. Summary Statistics

| Metric | Count | Notes |
|--------|-------|-------|
| **Total Tables** | 37 | 31 core migrations + 6 Laravel framework |
| **Total Columns** | ~450+ | Across all tables |
| **Foreign Key Relationships** | 50+ | Explicit FK constraints |
| **Composite Indexes** | 30+ | Multi-column indexes |
| **Unique Constraints** | 12 | Business key enforcement |
| **Soft Delete Tables** | 12 | Eloquent soft delete enabled |
| **Polymorphic Relations** | 1 | `notifications` table |
| **JSON Columns** | 20+ | For flexible/semi-structured data |
| **ENUM Columns** | 30+ | For constrained value sets |

### **Table Distribution by Layer**

| Layer | Tables | Percentage |
|-------|--------|-----------|
| Foundation | 2 | 5% |
| Client Management | 3 | 8% |
| Service Delivery | 5 | 13% |
| Attendance Management | 4 | 11% |
| Asset Management | 7 | 18% |
| Communication | 5 | 13% |
| Education Planning | 2 | 5% |
| Progress Reporting | 1 | 3% |
| System/Auxiliary | 4 | 11% |
| Laravel Framework | 4 | 11% |

### **Storage Estimates** (Production Data)

| Table | Estimated Rows (5 Centres, 5 Years) | Growth Rate |
|-------|-------------------------------------|-------------|
| trainees | 5,000 | 1,000/year |
| activities | 500 | 100/year |
| activity_occurrences | 50,000 | 10,000/year |
| activity_enrollments | 15,000 | 3,000/year |
| staff_attendances | 250,000 | 50,000/year |
| trainee_attendances | 500,000 | 100,000/year |
| session_attendance | 750,000 | 150,000/year |
| audit_logs | 10,000,000+ | 2M/year |
| notifications | 1,000,000+ | 200K/year |

**Total Estimated Storage (5 years):** 15-20 GB (data) + 10-15 GB (indexes) = **25-35 GB**

---

## Appendix A: Foreign Key Constraint Summary

```sql
-- CENTRES RELATIONSHIPS
ALTER TABLE staffs ADD CONSTRAINT fk_staffs_centre FOREIGN KEY (centre_id) REFERENCES centres(centre_id) ON DELETE RESTRICT;
ALTER TABLE trainees ADD CONSTRAINT fk_trainees_centre FOREIGN KEY (centre_id) REFERENCES centres(centre_id) ON DELETE RESTRICT;
ALTER TABLE volunteers ADD CONSTRAINT fk_volunteers_centre FOREIGN KEY (centre_id) REFERENCES centres(centre_id) ON DELETE RESTRICT;
ALTER TABLE activities ADD CONSTRAINT fk_activities_centre FOREIGN KEY (centre_id) REFERENCES centres(centre_id) ON DELETE RESTRICT;
ALTER TABLE assets ADD CONSTRAINT fk_assets_centre FOREIGN KEY (centre_id) REFERENCES centres(centre_id) ON DELETE CASCADE;
ALTER TABLE asset_locations ADD CONSTRAINT fk_asset_locations_centre FOREIGN KEY (centre_id) REFERENCES centres(centre_id) ON DELETE CASCADE;
ALTER TABLE letters ADD CONSTRAINT fk_letters_centre FOREIGN KEY (centre_id) REFERENCES centres(centre_id) ON DELETE CASCADE;
ALTER TABLE letter_templates ADD CONSTRAINT fk_letter_templates_centre FOREIGN KEY (centre_id) REFERENCES centres(centre_id) ON DELETE SET NULL;
ALTER TABLE contact_messages ADD CONSTRAINT fk_contact_messages_centre FOREIGN KEY (centre_id) REFERENCES centres(centre_id) ON DELETE SET NULL;

-- ACTIVITIES RELATIONSHIPS
ALTER TABLE activity_occurrences ADD CONSTRAINT fk_occurrences_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE;
ALTER TABLE activity_enrollments ADD CONSTRAINT fk_enrollments_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE;
ALTER TABLE activity_enrollments ADD CONSTRAINT fk_enrollments_trainee FOREIGN KEY (trainee_id) REFERENCES trainees(id) ON DELETE CASCADE;
ALTER TABLE iep_activity_goals ADD CONSTRAINT fk_iep_goals_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE SET NULL;

-- ATTENDANCE RELATIONSHIPS
ALTER TABLE session_attendance ADD CONSTRAINT fk_session_attendance_session FOREIGN KEY (session_id) REFERENCES activity_occurrences(id) ON DELETE CASCADE;
ALTER TABLE session_attendance ADD CONSTRAINT fk_session_attendance_trainee FOREIGN KEY (trainee_id) REFERENCES trainees(id) ON DELETE CASCADE;

-- ASSET RELATIONSHIPS
ALTER TABLE assets ADD CONSTRAINT fk_assets_category FOREIGN KEY (category_id) REFERENCES asset_categories(id) ON DELETE CASCADE;
ALTER TABLE assets ADD CONSTRAINT fk_assets_location FOREIGN KEY (location_id) REFERENCES asset_locations(id) ON DELETE SET NULL;
ALTER TABLE asset_maintenance ADD CONSTRAINT fk_maintenance_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE;

-- IEP RELATIONSHIPS
ALTER TABLE trainee_education_plans ADD CONSTRAINT fk_iep_trainee FOREIGN KEY (trainee_id) REFERENCES trainees(id) ON DELETE CASCADE;
ALTER TABLE iep_activity_goals ADD CONSTRAINT fk_iep_goals_iep FOREIGN KEY (iep_id) REFERENCES trainee_education_plans(id) ON DELETE CASCADE;

-- COMMUNICATION RELATIONSHIPS
ALTER TABLE messages ADD CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES staffs(id) ON DELETE CASCADE;
ALTER TABLE message_recipients ADD CONSTRAINT fk_recipients_message FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE;
ALTER TABLE letters ADD CONSTRAINT fk_letters_created_by FOREIGN KEY (created_by) REFERENCES staffs(id) ON DELETE CASCADE;
ALTER TABLE letters ADD CONSTRAINT fk_letters_template FOREIGN KEY (template_id) REFERENCES letter_templates(id) ON DELETE SET NULL;
```

---

**Document Status:** ✅ Complete
**Last Verification:** 2026-02-06
**Schema Version:** Based on migrations up to 2026-02-05
**Maintenance Schedule:** Update after each migration
