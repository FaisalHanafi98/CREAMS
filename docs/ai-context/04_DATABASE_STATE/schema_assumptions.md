# CREAMS — Schema Assumptions

**Last updated**: 2026-05-08
**Source**: migration files, model inspection, artisan migrate:status

---

## Core tables (VERIFIED — all 34 migrations ran)

| Table | Primary key | Centre isolation | Model | Notes |
|---|---|---|---|---|
| centres | centre_id (varchar 10) | N/A (is the scope) | Centre | centre_id is a string like 'UA1', '01', 'GBK' |
| staffs | id (bigint) | CentreScope | User (table='staffs') | Not `users` table — the model maps to staffs |
| trainees | id (bigint) + trainee_id (string) | CentreScope | Trainee | Has both auto-increment id and a business trainee_id |
| activities | id (bigint) | CentreScope | Activity | activity_name + centre_id should be unique |
| activity_occurrences | id (bigint) | Via activity | ActivitySession | aka "sessions" |
| activity_enrollments | id (bigint) | Via activity | ActivityEnrollment | trainee_id is bigint FK to trainees.id |
| session_attendance | id (bigint) | Via session | — | session_id → activity_occurrences.id |
| trainee_attendances | id (bigint) | Via trainee | Attendance | Used by Trainee model's attendances() relationship |
| staff_attendances | id (bigint) | Via user | — | user_id → staffs.id |
| volunteers | id (bigint) | None | Volunteer | No centre_id column |
| assets | id (bigint) | CentreScope | Asset | |
| letters | id (bigint) | Via staff | — | |
| messages | id (bigint) | CentreScope | — | |
| notifications | id (bigint) | Via staff | — | |

---

## Critical model-table mapping gotchas

1. **User model → staffs table**: `class User extends Authenticatable { protected $table = 'staffs'; }`. Do NOT query `DB::table('users')` — it doesn't exist.

2. **Trainee has TWO IDs**: `id` (auto-increment bigint, used as FK target) and `trainee_id` (business string, format `UAT-UA1-001`). Eloquent uses `id`. Business logic displays `trainee_id`.

3. **ActivityEnrollment.trainee_id is bigint**: It references `trainees.id` (the auto-increment), NOT `trainees.trainee_id` (the string).

4. **activity_occurrences vs activity_sessions**: The migration file calls the table `activity_occurrences` but the model may be called `ActivitySession`. Use the table name from migrations, not model class names.

5. **session_attendance vs trainee_attendances**: TWO separate attendance tables.
   - `session_attendance`: records per session (used for session-level tracking)
   - `trainee_attendances`: records per trainee (used by `Trainee::attendances()` relationship and `getAttendanceStatistics()`)
   - Both must be seeded for full attendance reporting.

6. **phone column NOT NULL in staffs**: The `staffs.phone` column is `varchar(20) NOT NULL` with no default. Any INSERT must include `phone`.

7. **session_notes TEXT column**: `activity_occurrences.session_notes` is TEXT. Cannot have a DEFAULT value in MySQL. Must be NULL or provided.

---

## Centre isolation pattern

```php
// Pattern 1: CentreScope global scope (23 models)
protected static function booted(): void
{
    static::addGlobalScope(new CentreScope);
}

// Pattern 2: Closure scope for models without direct centre_id
static::addGlobalScope('centre_isolation', function ($query) {
    $query->whereHas('asset', function ($q) {
        $q->where('centre_id', session('centre_id'));
    });
});
```

CentreScope reads `session('centre_id')` set by `Authenticate` middleware. Admin users bypass via `session('role') === 'admin'`.

---

## Known schema mismatches (POSSIBLE DEFECT)

1. **Category.php model**: `protected $table = 'activity_categories'` — table was dropped by migration `2025_09_28_164108`. Model kept as orphan. Do not call it.

2. **AssetCategories.php vs AssetCategory.php**: Two models for similar purpose. INFERRED they reference different tables. Do not use `AssetCategories` without verifying its table exists.

3. **volunteers table**: Has no `centre_id` — intentional for public volunteer applications. CentreScope is NOT applied. All roles can see all volunteers.
