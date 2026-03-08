# 🏗️ CREAMS Schema Audit Report
## Controlled Architectural Reset

**Date:** 2026-01-31
**Auditor:** Multi-Actor System (System Architect, Backend Engineer, Database Architect, QA Engineer, Domain Expert)
**Status:** 🔴 PENDING APPROVAL - DO NOT IMPLEMENT
**Current State:** Working system with 10/46 tests passing (21.7% coverage)

---

## ⚠️ EXECUTIVE SUMMARY

**CRITICAL FINDING:** The `staffs` table does NOT exist. The system currently uses `users` table for authentication.

**Proposed Changes:**
- **Tables to DELETE:** 13 tables
- **Tables to CREATE:** 1 new table (staffs)
- **Tables to RENAME:** 2 tables
- **Tables to MERGE:** 3 tables
- **Tables to KEEP:** 25 tables
- **Data Migration Required:** 129 users → staffs table

**Impact Assessment:**
- 🔴 **BREAKING:** All authentication will fail
- 🔴 **BREAKING:** All current tests will fail
- 🔴 **BREAKING:** All foreign key relationships to users must be updated
- ⚠️ **MIGRATION:** 129 existing user records must be migrated
- ⚠️ **CODE CHANGES:** ~50+ files affected (controllers, middleware, models, tests)

---

## 📋 NON-NEGOTIABLE ARCHITECTURAL DECISIONS (REVIEW)

### ✅ 1. Activities - Category Denormalization
**Requirement:** No `activity_categories` table, use enum column
**Status:** ✅ **ALREADY COMPLETED** (Fixed in previous session)
**Action:** None required

### 🔴 2. Assets - Single Table Pattern
**Requirement:** Single `assets` table, category/location as columns, no auxiliary tables
**Status:** ❌ **NOT COMPLIANT** - 6 asset-related tables exist
**Action:** DELETE 5 tables, restructure assets table

### 🔴 3. Attendance - Direct Occurrence Tracking
**Requirement:** No `sessions` table, direct activity occurrence tracking
**Status:** ❌ **NOT COMPLIANT** - `sessions` and `session_attendance` tables exist
**Action:** DELETE 2 tables, restructure attendance tracking

### 🔴 4. Identity - Staffs as Sole Auth Source
**Requirement:** Remove `users` table, use `staffs` table for auth
**Status:** ❌ **CRITICAL NON-COMPLIANCE** - `staffs` table DOES NOT EXIST
**Action:** CREATE staffs table, MIGRATE 129 users, DELETE users table

---

## 🗂️ TABLE-BY-TABLE AUDIT

### 📦 ACTIVITIES DOMAIN

#### ✅ KEEP: `activities`
**Current State:** 0 rows
**Justification:** Core domain table, already denormalized (category is string column)
**Dependencies:** activity_enrollments, activity_sessions, activity_logs, trainee_attendances, staff_attendances
**Action:** None - Compliant with architecture

#### ✅ KEEP: `activity_enrollments`
**Current State:** 0 rows
**Justification:** Tracks trainee enrollment in activities (many-to-many relationship)
**Dependencies:** activities, trainees
**Action:** Verify FK references after identity migration

#### 🟡 REVIEW: `activity_logs`
**Current State:** 0 rows
**Justification:** Audit trail for activity changes
**Dependencies:** activities, users (FK to created_by/updated_by)
**Action:** UPDATE foreign keys from `users` → `staffs` after migration
**Migration Risk:** HIGH - Must preserve audit trail integrity

#### 🔴 DELETE: `activity_sessions` (DECISION REQUIRED)
**Current State:** 0 rows
**Architectural Conflict:** Requirement states "No sessions table"
**Current Usage:** Tracks scheduled sessions/occurrences of activities
**Dependencies:** session_attendance, staff_attendances, notifications

**System Architect Decision Needed:**
- **Option A:** DELETE - Replace with calculated occurrence logic (complex)
- **Option B:** RENAME to `activity_occurrences` - Keep structure, align terminology
- **Option C:** KEEP - Exception to rule if justified by domain requirements

**Domain Expert Input Required:**
Does the rehabilitation centre need to:
- Track individual session occurrences?
- Record session-specific attendance?
- Schedule recurring vs one-time activities?

**Recommended:** RENAME to `activity_occurrences` (preserves functionality, aligns terminology)

---

### 📦 ASSETS DOMAIN

#### 🟡 RESTRUCTURE: `assets`
**Current State:** 0 rows
**Required Changes:**
1. ADD column: `category` (varchar/enum) - migrate from asset_categories join
2. ADD column: `location` (varchar/enum) - migrate from asset_locations join
3. KEEP column: `parent_asset_id` (bigint unsigned, nullable) - justified for hierarchical assets (e.g., laptop → IT equipment)
4. DROP foreign keys to asset_categories, asset_locations

**Justification:** Single-table pattern reduces joins, improves query performance
**Migration Complexity:** LOW - No data exists yet

#### 🔴 DELETE: `asset_categories`
**Current State:** 0 rows
**Justification:** Violates single-table pattern, migrate to assets.category column
**Dependencies:** assets table (FK constraint)
**Impacted Files:**
- `app/Models/AssetCategory.php` - DELETE model
- `app/Http/Controllers/Asset/*` - Remove category joins
- Asset views/forms - Change to select/dropdown
**Migration:**
```sql
-- Add category column to assets
ALTER TABLE assets ADD COLUMN category VARCHAR(100) AFTER asset_name;

-- Migrate existing data (if any)
UPDATE assets a
JOIN asset_categories ac ON a.category_id = ac.id
SET a.category = ac.category_name;

-- Drop FK and column
ALTER TABLE assets DROP FOREIGN KEY fk_assets_category;
ALTER TABLE assets DROP COLUMN category_id;

-- Drop table
DROP TABLE asset_categories;
```

#### 🔴 DELETE: `asset_locations`
**Current State:** 0 rows
**Justification:** Same as asset_categories - migrate to column
**Dependencies:** assets table (FK constraint)
**Impacted Files:**
- `app/Models/AssetLocation.php` - DELETE model
- `app/Http/Controllers/Asset/*` - Remove location joins
**Migration:** Similar to asset_categories (add column, migrate, drop table)

#### 🔴 DELETE: `asset_parents`
**Current State:** 0 rows
**Justification:** Redundant - parent relationship handled by `parent_asset_id` in assets table
**Dependencies:** None (likely unused table)
**Action:** DROP table immediately

#### 🟡 REVIEW: `asset_maintenance`
**Current State:** 0 rows
**Justification:** Legitimate domain requirement for tracking maintenance schedules/history
**Decision Required:** Keep as separate table or denormalize into assets?
**Recommendation:** **KEEP** - Maintenance is 1-to-many with temporal data (separate table justified)

#### 🔴 DELETE: `asset_maintenance_history_backup`
**Current State:** 0 rows
**Justification:** Backup table - should not be in production schema
**Action:** DROP immediately, implement proper backup strategy (mysqldump, snapshots)

#### 🔴 DELETE: `asset_movements_backup`
**Current State:** 0 rows
**Justification:** Backup table - should not be in production schema
**Action:** DROP immediately

---

### 📦 ATTENDANCE DOMAIN

#### 🔴 DELETE: `sessions` (CRITICAL DECISION)
**Current State:** 0 rows
**Architectural Requirement:** "No sessions table"
**Current Dependencies:**
- session_attendance (FK)
- staff_attendances (FK)
- notifications (FK)

**System Architect Decision Required:**
This is the CORE of the attendance architecture change.

**Current Model (Sessions-Based):**
```
activities → sessions (scheduled occurrences) → session_attendance
                    ↓
              staff_attendances
```

**Proposed Model (Direct Occurrence):**
```
activities → trainee_activity_attendances (date-based)
          → staff_attendances (date-based)
```

**Critical Questions:**
1. How are "occurrences" identified without sessions table?
   - By date only? (2026-02-01)
   - By date + sequence number? (2026-02-01, occurrence #3)
   - By calculated schedule logic? (Activity runs Mon/Wed, infer occurrences)

2. How is attendance recorded?
   - `trainee_activity_attendances (activity_id, trainee_id, occurrence_date, status)`
   - Staff same pattern?

3. How are sessions scheduled in advance?
   - No pre-creation → attendance creates occurrence on-the-fly?
   - Pre-calculate occurrences from activity schedule?

**Recommendation:**
- **IF** occurrences are pre-scheduled → RENAME `sessions` to `activity_occurrences`
- **IF** attendance is ad-hoc → DELETE `sessions`, add `occurrence_date` to attendance tables

**Domain Expert Input REQUIRED before proceeding.**

#### 🔴 DELETE: `session_attendance`
**Current State:** 0 rows
**Justification:** Replaced by direct trainee_activity_attendances
**Dependencies:** Cascade from sessions table deletion
**Action:** DROP after sessions decision is finalized

#### 🟢 RENAME: `trainee_attendances` → `trainee_activity_attendances`
**Current State:** 0 rows
**Justification:** Aligns with architectural requirement naming
**Current Structure:**
```sql
DESCRIBE trainee_attendances;
```
**Required Changes:**
1. RENAME table
2. ADD `occurrence_date` column (if sessions deleted)
3. ADD `occurrence_sequence` column (optional, for same-day multiple sessions)
4. UPDATE foreign key `session_id` → remove OR change to `activity_occurrence_id`

**Migration:**
```sql
RENAME TABLE trainee_attendances TO trainee_activity_attendances;

-- If removing sessions FK:
ALTER TABLE trainee_activity_attendances DROP FOREIGN KEY fk_session_id;
ALTER TABLE trainee_activity_attendances DROP COLUMN session_id;
ALTER TABLE trainee_activity_attendances ADD COLUMN occurrence_date DATE NOT NULL;
ALTER TABLE trainee_activity_attendances ADD COLUMN occurrence_sequence INT DEFAULT 1;
```

**Impacted Files:**
- `app/Models/TraineeAttendance.php` → RENAME to TraineeActivityAttendance.php
- `app/Http/Controllers/Attendance/*` - Update model references
- All views/forms using attendance

#### ✅ KEEP: `staff_attendances`
**Current State:** 0 rows
**Justification:** Tracks staff daily attendance (not session-specific per requirement)
**Required Changes:** Update FK from `users.id` → `staffs.id` after identity migration
**Action:** ALTER foreign key constraint

#### ✅ KEEP: `attendance_alerts`
**Current State:** 0 rows
**Justification:** Business logic for attendance monitoring
**Required Changes:** Verify FK references after other attendance changes

---

### 📦 IDENTITY & AUTHENTICATION DOMAIN (CRITICAL)

#### 🔴 DELETE: `users` → 🟢 CREATE: `staffs`
**Current State:** **129 users** (POPULATED!)
**Architectural Requirement:** "staffs table is the sole authenticated identity source"

**CRITICAL FINDING:** `staffs` table DOES NOT EXIST!

**Current `users` Table Structure:**
```
id, iium_id, name, email, email_verified_at, password, phone, address,
education_level, education_specialization, teaching_specialization,
date_of_birth, role (admin/supervisor/teacher/ajk), status, centre_id,
encrypted_id, avatar, position, about, centre_location,
user_last_accessed_at, remember_token, created_at, updated_at, deleted_at
```

**Proposed `staffs` Table:**
```sql
CREATE TABLE staffs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'e.g., STF001, IIUM ID',
    iium_id VARCHAR(50) UNIQUE COMMENT 'IIUM Staff ID',

    -- Identity
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),

    -- Contact
    phone VARCHAR(20),
    address TEXT,

    -- Professional
    education_level VARCHAR(100),
    education_specialization VARCHAR(255),
    teaching_specialization VARCHAR(255),
    position VARCHAR(100),

    -- Role & Assignment
    role ENUM('admin', 'supervisor', 'teacher', 'ajk') NOT NULL DEFAULT 'teacher',
    status ENUM('active', 'inactive', 'pending', 'terminated') NOT NULL DEFAULT 'pending',
    centre_id VARCHAR(10),

    -- Personal
    date_of_birth DATE,
    avatar VARCHAR(255),
    about TEXT,

    -- Audit
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    -- Foreign Keys
    FOREIGN KEY (centre_id) REFERENCES centres(centre_id) ON DELETE SET NULL,

    -- Indexes
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_centre (centre_id),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Data Migration Required:**
```sql
-- Migrate 129 users to staffs
INSERT INTO staffs (
    staff_id, iium_id, name, email, email_verified_at, password,
    phone, address, education_level, education_specialization,
    teaching_specialization, position, role, status, centre_id,
    date_of_birth, avatar, about, remember_token,
    last_login_at, created_at, updated_at, deleted_at
)
SELECT
    CONCAT('STF', LPAD(id, 4, '0')) as staff_id,  -- Generate STF0001, STF0002, etc.
    iium_id, name, email, email_verified_at, password,
    phone, address, education_level, education_specialization,
    teaching_specialization, position, role, status, centre_id,
    date_of_birth, avatar, about, remember_token,
    user_last_accessed_at as last_login_at,
    created_at, updated_at, deleted_at
FROM users;

-- Verify migration
SELECT COUNT(*) FROM staffs;  -- Should be 129

-- CRITICAL: Update all foreign keys before dropping users table
```

**Impacted Files (Estimated 50+ files):**

**Models:**
- `app/Models/User.php` → DELETE
- CREATE `app/Models/Staff.php`
- All models with user relationships (Activity, Asset, etc.)

**Controllers:**
- `app/Http/Controllers/Auth/*` - Complete rewrite for Staff model
- `app/Http/Controllers/Staff/*` - Rename from User controllers
- All controllers using `Auth::user()` → `Auth::staff()`

**Middleware:**
- `app/Http/Middleware/Authenticate.php` - Update guard to 'staff'
- `app/Http/Middleware/RoleMiddleware.php` - Update to use Staff model

**Config:**
- `config/auth.php` - Change provider from 'users' to 'staffs'
```php
'providers' => [
    'staffs' => [
        'driver' => 'eloquent',
        'model' => App\Models\Staff::class,
    ],
],
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'staffs',  // Changed from 'users'
    ],
],
```

**Database - Foreign Key Updates:**
```sql
-- Find all tables with user_id foreign keys
SELECT
    TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_NAME = 'users'
AND TABLE_SCHEMA = 'cream';

-- Update each FK constraint (example):
ALTER TABLE activity_logs
    DROP FOREIGN KEY fk_activity_logs_user,
    ADD CONSTRAINT fk_activity_logs_staff
    FOREIGN KEY (created_by) REFERENCES staffs(id);
```

**Tests:**
- `tests/Browser/auth.setup.ts` - Complete rewrite
- ALL test files using authentication (46+ test files)
- Update test credentials/seeding

**Views:**
- All Blade templates using `auth()->user()` → `auth()->staff()`
- Profile pages, dashboards, navigation

**Migrations:**
- CREATE `create_staffs_table` migration
- CREATE data migration `migrate_users_to_staffs`
- CREATE FK update migrations for all dependent tables
- CREATE `drop_users_table` migration (FINAL step)

**Risk Assessment:** 🔴 **CRITICAL - BREAKING CHANGE**
- Estimated Implementation Time: 2-3 days
- Testing Time: 1-2 days
- Rollback Strategy: Keep users table until staffs fully verified
- Phased Approach Recommended:
  1. CREATE staffs table
  2. MIGRATE data
  3. UPDATE code to use both (feature flag)
  4. TEST thoroughly
  5. SWITCH to staffs only
  6. DROP users table

#### 🟡 REVIEW: `password_resets`
**Current State:** 0 rows
**Justification:** Password reset tokens - typically tied to user email
**Required Changes:**
- **Option A:** RENAME to `staff_password_resets` (align with staffs table)
- **Option B:** KEEP as-is (generic table, email-based lookup)
**Recommendation:** KEEP as-is (email is lookup key, table name doesn't matter)

#### 🟡 REVIEW: `personal_access_tokens`
**Current State:** 0 rows
**Justification:** Laravel Sanctum API tokens
**Required Changes:** Update `tokenable_type` from `App\Models\User` → `App\Models\Staff`
**Migration:**
```sql
UPDATE personal_access_tokens
SET tokenable_type = 'App\\Models\\Staff'
WHERE tokenable_type = 'App\\Models\\User';
```
**Recommendation:** KEEP table, update polymorphic references

---

### 📦 TRAINEES DOMAIN

#### ✅ KEEP: `trainees`
**Current State:** 0 rows
**Justification:** Core domain table, separate from staff identity
**Action:** None - Compliant

---

### 📦 CENTRES DOMAIN

#### ✅ KEEP: `centres`
**Current State:** 4 centres (Gombak, Kuantan, KL, Pagoh) - VERIFIED in previous session
**Justification:** Core domain table
**Dependencies:** activities, staffs (FK), trainees (FK)
**Action:** None - Compliant

---

### 📦 COMMUNICATION & NOTIFICATIONS

#### ✅ KEEP: `notifications`
**Current State:** 407 rows
**Justification:** Laravel notifications table (polymorphic)
**Required Changes:** Update `notifiable_type` from `App\Models\User` → `App\Models\Staff`
**Migration:**
```sql
UPDATE notifications
SET notifiable_type = 'App\\Models\\Staff'
WHERE notifiable_type = 'App\\Models\\User';
```

#### ✅ KEEP: `messages`
**Current State:** 257 rows
**Justification:** Internal messaging system
**Required Changes:** Update sender_id/receiver_id FK from users → staffs
**Action:** ALTER foreign key constraints

#### ✅ KEEP: `message_recipients`
**Current State:** 257 rows
**Justification:** Message delivery tracking
**Required Changes:** Update recipient_id FK from users → staffs

#### ✅ KEEP: `letters`
**Current State:** 259 rows
**Justification:** Official correspondence tracking
**Required Changes:** Update created_by FK from users → staffs

#### ✅ KEEP: `letter_templates`
**Current State:** 5 rows
**Justification:** Letter template library
**Action:** None

#### ✅ KEEP: `contact_messages`
**Current State:** 0 rows
**Justification:** Public contact form submissions
**Action:** None

---

### 📦 SYSTEM & INFRASTRUCTURE

#### ✅ KEEP: `audit_logs`
**Current State:** 0 rows
**Justification:** System-wide audit trail
**Required Changes:** Update user_id FK from users → staffs
**Critical:** Preserve existing audit data during migration

#### ✅ KEEP: `public_holidays`
**Current State:** 0 rows
**Justification:** Calendar/scheduling reference data
**Action:** None

#### ✅ KEEP: `failed_jobs`
**Current State:** 0 rows
**Justification:** Laravel queue system
**Action:** None

#### ✅ KEEP: `jobs`
**Current State:** 0 rows
**Justification:** Laravel queue system
**Action:** None

#### ✅ KEEP: `migrations`
**Justification:** Laravel migration tracking
**Action:** None

---

### 📦 VOLUNTEERS (OUT OF SCOPE?)

#### 🟡 REVIEW: `volunteers`
**Current State:** 0 rows
**Justification:** Unknown - not mentioned in architectural requirements
**Questions:**
- Are volunteers a separate identity type?
- Do they authenticate? (If yes, contradicts "staffs sole auth source")
- Should they be merged into staffs with role='volunteer'?
**Recommendation:** Clarify domain requirements, likely MERGE into staffs

---

### 📦 DATABASE VIEWS

#### 🟡 REVIEW: `v_active_trainees`
**Justification:** Convenience view for active trainees
**Action:** Verify view definition doesn't reference deleted tables

#### 🟡 REVIEW: `v_activity_summary`
**Justification:** Reporting/analytics view
**Action:** Verify view definition, update if referencing deleted tables

#### 🟡 REVIEW: `v_attendance_rates`
**Justification:** Reporting view
**Action:** Verify and update for new attendance structure

#### 🟡 REVIEW: `v_data_integrity_check`
**Justification:** Data quality monitoring
**Action:** Update to check new schema constraints

---

## 📋 SUMMARY TABLES

### Tables to DELETE (13 tables)

| Table | Reason | Data Impact | Migration Complexity |
|-------|--------|-------------|---------------------|
| `activity_sessions` | "No sessions" requirement | 0 rows | LOW - Alternative: RENAME to activity_occurrences |
| `session_attendance` | Cascade from sessions deletion | 0 rows | LOW |
| `asset_categories` | Migrate to assets.category column | 0 rows | LOW |
| `asset_locations` | Migrate to assets.location column | 0 rows | LOW |
| `asset_parents` | Redundant with parent_asset_id | 0 rows | LOW |
| `asset_maintenance_history_backup` | Backup table | 0 rows | LOW |
| `asset_movements_backup` | Backup table | 0 rows | LOW |
| `users` | Replace with staffs table | **129 rows** | **CRITICAL - HIGH** |
| `sessions` (Laravel) | Conflicts with domain sessions | 0 rows | MEDIUM - Session storage |
| `volunteers` | Merge into staffs? (TBD) | 0 rows | MEDIUM |

### Tables to CREATE (1 table)

| Table | Purpose | Dependencies | Priority |
|-------|---------|--------------|----------|
| `staffs` | Sole authentication identity | centres, all user_id FKs | **CRITICAL** |

### Tables to RENAME (2 tables)

| Current Name | New Name | Reason |
|--------------|----------|--------|
| `trainee_attendances` | `trainee_activity_attendances` | Architectural requirement naming |
| `activity_sessions` (OPTION) | `activity_occurrences` | Align terminology if keeping |

### Tables to RESTRUCTURE (2 tables)

| Table | Changes Required |
|-------|------------------|
| `assets` | ADD category column, ADD location column, DROP FKs to deleted tables |
| `trainee_activity_attendances` | ADD occurrence_date, REMOVE session_id FK (if sessions deleted) |

### Tables to KEEP (25 tables)

All remaining tables with potential FK updates from users → staffs:
- activities, activity_enrollments, activity_logs
- assets, asset_maintenance
- centres
- trainees
- staff_attendances, attendance_alerts
- notifications, messages, message_recipients, letters, letter_templates, contact_messages
- audit_logs, public_holidays
- failed_jobs, jobs, migrations
- 4 database views (v_*)

---

## 🚨 CRITICAL DEPENDENCIES & MIGRATION ORDER

### Phase 1: Preparation (No Breaking Changes)
1. ✅ CREATE `staffs` table
2. ✅ MIGRATE data from users → staffs (129 records)
3. ✅ VERIFY data integrity

### Phase 2: Asset Domain (No Data Impact)
4. ALTER `assets` table (add category, location columns)
5. DROP `asset_categories` table
6. DROP `asset_locations` table
7. DROP `asset_parents` table
8. DROP `asset_*_backup` tables

### Phase 3: Attendance Domain (DECISION REQUIRED)
**BLOCKED until System Architect decides on sessions table**

**Option A: Delete Sessions**
9. ALTER `trainee_attendances` (add occurrence_date, drop session_id)
10. RENAME `trainee_attendances` → `trainee_activity_attendances`
11. DROP `session_attendance`
12. DROP `sessions`

**Option B: Keep Sessions (Renamed)**
9. RENAME `sessions` → `activity_occurrences`
10. RENAME `trainee_attendances` → `trainee_activity_attendances`
11. UPDATE FK constraints

### Phase 4: Identity Migration (BREAKING CHANGES)
13. UPDATE all code references users → staffs (50+ files)
14. UPDATE config/auth.php
15. UPDATE all foreign keys users.id → staffs.id
16. UPDATE polymorphic types (notifications, tokens)
17. RUN complete test suite
18. VERIFY authentication works

### Phase 5: Cleanup
19. DROP `users` table (FINAL STEP - NO ROLLBACK)
20. VERIFY all tests passing
21. UPDATE documentation

---

## 📊 IMPACT ASSESSMENT

### Code Changes Required

| Category | Estimated Files | Complexity | Risk |
|----------|----------------|------------|------|
| Models | 15-20 | HIGH | HIGH |
| Controllers | 25-30 | HIGH | HIGH |
| Middleware | 3-5 | MEDIUM | HIGH |
| Config | 2-3 | LOW | CRITICAL |
| Migrations | 10-15 | HIGH | CRITICAL |
| Tests | 46+ | HIGH | MEDIUM |
| Views | 30-40 | MEDIUM | LOW |
| **TOTAL** | **131-159 files** | **HIGH** | **CRITICAL** |

### Data Migration Risks

| Table | Current Rows | Risk Level | Mitigation |
|-------|--------------|------------|------------|
| users → staffs | 129 | CRITICAL | Backup, verify, phased rollout |
| notifications | 407 | MEDIUM | Update polymorphic types, verify |
| messages | 257 | MEDIUM | Update FKs, verify delivery |
| letters | 259 | MEDIUM | Update FKs, preserve audit trail |
| All others | 0 | LOW | No data loss risk |

### Testing Impact

| Test Category | Current Status | Post-Migration Status | Effort |
|---------------|----------------|----------------------|--------|
| Authentication | ✅ Working (4 roles) | 🔴 All broken | 2-3 days |
| Activity CRUD | ⚠️ 5/28 passing | 🔴 0/28 passing | 1-2 days |
| Trainee CRUD | ⚠️ 2/15 passing | 🔴 0/15 passing | 1 day |
| Staff CRUD | ✅ 3/3 passing | 🔴 0/3 passing | 1 day |
| **TOTAL** | 10/46 (21.7%) | **0/46 (0%)** | **5-8 days** |

---

## 🎯 RECOMMENDATIONS

### 🔴 CRITICAL: DO NOT PROCEED WITHOUT APPROVAL

This architectural reset will:
1. ✅ Align with stated architectural principles
2. ✅ Simplify schema (fewer tables, fewer joins)
3. ✅ Improve query performance (denormalization)
4. 🔴 BREAK all current functionality
5. 🔴 REQUIRE complete system rewrite (50+ files)
6. 🔴 INVALIDATE all current tests
7. 🔴 REQUIRE 129-user data migration

### 📝 REQUIRED DECISIONS (System Architect)

**Before ANY code changes:**

1. **Sessions Table Decision:**
   - [ ] DELETE sessions → Use occurrence_date in attendance tables
   - [ ] RENAME sessions → activity_occurrences (keep structure)
   - [ ] KEEP sessions as exception to architecture (justify)

2. **Volunteers Table Decision:**
   - [ ] DELETE volunteers → Merge into staffs with role='volunteer'
   - [ ] KEEP volunteers as separate identity type (justify vs "staffs sole auth")

3. **Migration Strategy:**
   - [ ] Big Bang (all changes at once) - Fast but risky
   - [ ] Phased (domain by domain) - Slower but safer
   - [ ] Parallel (run both users + staffs) - Safest but complex

4. **Rollback Strategy:**
   - [ ] Keep users table as backup for 30 days
   - [ ] Full database snapshot before migration
   - [ ] Feature flags to switch between users/staffs

### 🎯 IF APPROVED: Recommended Migration Sequence

**Week 1: Non-Breaking Changes**
- Day 1-2: Asset domain restructure
- Day 3-4: Create staffs table, migrate data
- Day 5: Attendance domain changes (after sessions decision)

**Week 2: Breaking Changes (Code)**
- Day 1-2: Update models and controllers
- Day 3-4: Update middleware, config, guards
- Day 5: Code review and integration testing

**Week 3: Testing & Validation**
- Day 1-2: Rewrite authentication tests
- Day 3-4: Rewrite all CRUD tests
- Day 5: End-to-end testing

**Week 4: Final Migration**
- Day 1-2: Production migration rehearsal
- Day 3: GO/NO-GO decision
- Day 4: Production migration
- Day 5: Monitor and hotfix

**Total Estimated Time:** 4 weeks (20 business days)

---

## ✅ APPROVAL CHECKLIST

**This report must be approved by ALL actors before proceeding:**

- [ ] **System Architect** - Confirm architectural decisions are correct
- [ ] **Laravel Backend Engineer** - Confirm code changes are feasible
- [ ] **Database Architect** - Confirm migrations are safe and reversible
- [ ] **QA / Playwright Engineer** - Confirm test rewrite scope is acceptable
- [ ] **Domain Expert** - Confirm sessions/attendance model serves business needs

**Additional Approvals Required:**
- [ ] **Product Owner** - Accept 4-week timeline and feature freeze
- [ ] **Technical Lead** - Accept risk assessment and rollback strategy

---

## 📎 APPENDIX: SQL MIGRATION SCRIPTS (DRAFT)

### A. Create Staffs Table
```sql
-- See detailed CREATE TABLE statement in Identity section above
-- Located at: database/migrations/YYYY_MM_DD_create_staffs_table.php
```

### B. Migrate Users to Staffs
```sql
-- See detailed INSERT statement in Identity section above
-- Located at: database/migrations/YYYY_MM_DD_migrate_users_to_staffs.php
```

### C. Update Foreign Keys (Sample)
```sql
-- Example for activity_logs table
ALTER TABLE activity_logs
    DROP FOREIGN KEY fk_activity_logs_user_created,
    DROP FOREIGN KEY fk_activity_logs_user_updated;

ALTER TABLE activity_logs
    ADD CONSTRAINT fk_activity_logs_staff_created
        FOREIGN KEY (created_by) REFERENCES staffs(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_activity_logs_staff_updated
        FOREIGN KEY (updated_by) REFERENCES staffs(id) ON DELETE SET NULL;

-- Repeat for all 20+ tables with user_id FKs
```

### D. Asset Table Restructure
```sql
ALTER TABLE assets
    ADD COLUMN category VARCHAR(100) AFTER asset_name,
    ADD COLUMN location VARCHAR(100) AFTER category,
    ADD INDEX idx_category (category),
    ADD INDEX idx_location (location);

-- If data exists:
-- UPDATE assets a
-- JOIN asset_categories ac ON a.category_id = ac.id
-- SET a.category = ac.category_name;
-- UPDATE assets a
-- JOIN asset_locations al ON a.location_id = al.id
-- SET a.location = al.location_name;

ALTER TABLE assets
    DROP FOREIGN KEY fk_assets_category,
    DROP FOREIGN KEY fk_assets_location,
    DROP COLUMN category_id,
    DROP COLUMN location_id;

DROP TABLE asset_categories;
DROP TABLE asset_locations;
DROP TABLE asset_parents;
DROP TABLE asset_maintenance_history_backup;
DROP TABLE asset_movements_backup;
```

---

**END OF AUDIT REPORT**

**Status:** 🔴 AWAITING MULTI-ACTOR APPROVAL
**Next Step:** Review and approve/reject each section
**Do NOT implement:** Any changes until full approval received

---

*Generated: 2026-01-31*
*Auditor: Multi-Actor System*
*Review Required By: System Architect, Backend Engineer, Database Architect, QA Engineer, Domain Expert*
