# CREAMS — Seeders Status

**Last updated**: 2026-05-08

---

## Active seeders

### UATSeeder (PRIMARY — use for all non-local deployments)

**File**: `database/seeders/UATSeeder.php`
**Status**: MODIFIED locally (not committed as of 2026-05-08). Complete rewrite from sprint version.
**Dependency**: Calls `DemoSampleUsersSeeder` which is UNTRACKED — must be committed together.
**PDPA safe**: YES — uses Faker Malaysian locale, no real data.

**New version data volume** (from rewrite):
- 3 centres (Gombak, Kuantan, Pagoh) — realistic Malaysian centre names with addresses
- 20 trainees per centre = 60 total
- 6 activities per centre = 18 total
- Staff: 1 super admin + per centre (1 admin, 2 supervisors, 4 teachers, 2 ajk) = 28 total
- Sessions, enrollments, attendance records seeded

**Old version data volume** (sprint baseline — committed at 80d3c3b):
- 3 centres (UAT Centre A/B/C — anonymised labels)
- 7 trainees per centre = 21 total
- 3 activities per centre = 9 total
- 17 staff total
- 36 sessions, 45 enrollments, 135 session_attendance, 135 trainee_attendances, 80 staff_attendances

**Command**:
```bash
php artisan db:seed --class=UATSeeder --force
```

**Pre-requisite**: Must run with dev dependencies installed (Faker is in require-dev):
```bash
composer install           # includes Faker
php artisan db:seed --class=UATSeeder --force
composer install --no-dev --optimize-autoloader  # back to production
```

---

### DemoSampleUsersSeeder (UNTRACKED — must commit)

**File**: `database/seeders/DemoSampleUsersSeeder.php`
**Status**: UNTRACKED (exists locally, not in git). Called by new UATSeeder.
**Action required**: Stage and commit with UATSeeder changes.

---

### IRLSeeder (FORBIDDEN outside local)

**File**: `database/seeders/IRLSeeder.php`
**Status**: Hard-gated. Throws `RuntimeException` if `APP_ENV !== 'local'`.
**PDPA warning**: Contains or references real PPDK data. Never run on staging/production/UAT environments.
**Enforcement**: Code-level guard in `IRLSeeder::run()` line 29-34.

---

### Other seeders (DO NOT USE for standard development)

| Seeder | Purpose | Status |
|---|---|---|
| `CREAMSSeeder*` (7 files) | Old production data seeders | STALE — pre-sprint era |
| `DatabaseSeeder.php` | Default Laravel seeder | Calls nothing by default |
| `DataQualityImprovementSeeder.php` | One-time data fix | Not for routine use |
| `GombakDataExtractor.php` | Gombak centre data | NOT SAFE — may reference real data |
| `PublicHolidaySeeder.php` | Malaysian holidays table | Safe, can be run standalone |
| `TestDataSeeder.php` / `TestingGuideDataSeeder.php` | Testing data | OK for test environments |
| `ActivityLogSeeder.php` | Activity log generation | Situational |
| `CREAMSRealisticAttendanceSeeder.php` | Old realistic data | STALE |

---

## Seeder order issues

The UATSeeder wraps everything in a transaction. If any step fails, all changes are rolled back. Key ordering:
1. Centres must be seeded before staff (FK: staffs.centre_id → centres.centre_id)
2. Staff must be seeded before activities (FK: activities.instructor_id → staffs.id)
3. Activities must be seeded before sessions
4. Sessions must be seeded before session_attendance
5. Trainees must be seeded before enrollments and attendance

---

## Seeder quick reference

```bash
# Standard UAT seed (recommended)
php artisan db:seed --class=UATSeeder --force

# Full fresh with seed
php artisan migrate:fresh --seeder=UATSeeder --force   # LOCAL ONLY

# Check what's seeded
php artisan tinker --execute="echo DB::table('staffs')->count().' staff | '.DB::table('trainees')->count().' trainees | '.DB::table('centres')->count().' centres';"
```
