# Multi-Centre Data Isolation

**Document Type**: Security Architecture Reference
**Last Updated**: 2026-04-25
**Scope**: All Eloquent models in `app/Models/`

---

## Strategy

CREAMS enforces multi-centre data isolation via two complementary mechanisms:

**Mechanism 1 — CentreScope class** (`App\Models\Scopes\CentreScope`): applied to models that have a direct `centre_id` column. Automatically appends `WHERE centre_id = :session_centre_id` to every Eloquent query.

**Mechanism 2 — `centre_isolation` closure scope**: applied to models that have **no** `centre_id` column but belong to an asset. Filters via `whereHas('asset', fn($q) => $q->where('assets.centre_id', $centreId))` — DB-level subquery, not application-layer filtering.

Both mechanisms read `session('role')` and `session('centre_id')` at query time:
- No session data: no filter (console commands, queue workers, unauthenticated requests)
- Role is `admin`: no filter (admins see all centres)
- All other roles: filter by `centre_id` from session

---

## Scoped Models — Mechanism 1: CentreScope (23 models with direct `centre_id` column)

The following models have `static::addGlobalScope(new CentreScope)` in their `booted()` method:

| Model | Table | Notes |
|-------|-------|-------|
| Trainee | trainees | Core client data |
| Activity | activities | Scheduling |
| Asset | assets | Equipment |
| User | users / staffs | Staff accounts |
| Staff | staffs | Staff profiles |
| Supervisor | supervisors | Supervisor profiles |
| Teacher | teachers | Teacher profiles |
| AJK | ajks | Committee members |
| Volunteer | volunteers | Volunteer profiles |
| Admin | admins | Admin profiles |
| Letter | letters | Official correspondence |
| LetterTemplate | letter_templates | Letter templates |
| ActivityLog | activity_logs | Audit trail |
| ActivityScheduleTemplate | activity_schedule_templates | Schedule templates |
| StaffAttendance | staff_attendances | Attendance records |
| CentreStatistics | centre_statistics | Aggregated stats |
| CentreAuditLog | centre_audit_logs | Centre audit trail |
| AssetParent | asset_parents | Asset categories |
| AssetEnhanced | assets_enhanced | Extended asset data |
| AssetLocation | asset_locations | Asset locations |
| Event | events | Centre events |
| ClassModel | classes | Class records |
| MessageTemplate | message_templates | Message templates |

---

## Scoped Models — Mechanism 2: `centre_isolation` closure scope (2 models without `centre_id`)

These models have no `centre_id` column. Isolation routes through their `asset` relationship — the query subjoins `assets` and filters on `assets.centre_id`. Named scope `'centre_isolation'` is registered in each model's `booted()` method.

Bypass with `::withoutGlobalScope('centre_isolation')` when cross-centre access is needed (e.g. admin reports, queue workers).

| Model | Table | Isolation route | Test file |
|-------|-------|----------------|-----------|
| AssetMaintenance | asset_maintenance | `asset_id → assets.centre_id` | `tests/Feature/Security/AssetMaintenanceCentreIsolationTest.php` |
| AssetMovement | asset_movements | `asset_id → assets.centre_id` | `tests/Feature/Security/AssetMovementCentreIsolationTest.php` |

**Why these two differ**: The `asset_maintenance` and `asset_movements` tables were created without a `centre_id` column. Adding one would require a schema migration, backfill, and index on a table with potentially large row counts. The closure scope achieves the same security boundary at query time without a schema change.

---

## Documented Exceptions (2 models — no isolation applied)

### 1. `Message` (`messages` table)

**Why CentreScope is NOT applied**: The `messages` table has no `centre_id` column. The migration (`2025_01_01_000006_create_creams_communication_management_tables.php`) creates the table with only: `id`, `sender_id`, `subject`, `message_body`, `priority`, `status`, `sent_at`, `attachment_path`, `timestamps`.

**How isolation is enforced instead**: The `MessageController` enforces user-level isolation:
- `index()`: queries `WHERE sender_id = session('id')` — each user sees only their own sent messages.
- `show()`: checks `$message->sender_id != session('id')` and redirects with an error if the message was not sent by the current user.
- `destroy()`: same sender_id ownership check before deletion.

**Security properties**: A staff member at Centre A cannot view messages sent by Centre B staff because they would never have the `sender_id` of a Centre B user in their own query, and `show()` explicitly blocks cross-user access.

**Isolation test**: `tests/Feature/Security/MessageCentreIsolationTest.php` verifies these properties.

**Known design gap**: Isolation is at the application layer (controller), not the database layer. There is no DB-level `centre_id` column to enforce the boundary at query time. A future hardening step could add a `centre_id` column to the messages table and apply `CentreScope` for defence-in-depth. This is logged as a known architectural gap — not a blocker for the current production deployment given the controller-level checks are in place.

**Note on stale model code**: `Message.php` contains a `belongsTo(Centre::class, 'centre_id')` relationship, a `scopeForCentre()` local scope, and a `createBroadcast()` method that passes `centre_id` — all of which reference a column that does not exist in the database. These methods are never called by any controller and fail silently (mass assignment is blocked since `centre_id` is not in `$fillable`). They are left in place to avoid scope creep in this security stabilisation pass.

---

### 2. `Centre` (`centres` table)

**Why CentreScope is NOT applied**: `Centre` is the tenant entity itself. It is the root record that all other models reference via `centre_id`. Applying `CentreScope` to the `Centre` model would create a circular dependency — the scope would try to filter centres by `session('centre_id')`, but the session is populated by reading a Centre record. A centre cannot be its own gate.

**How access is controlled instead**: Access to `Centre` data is controlled at the controller and policy layer:
- Only admins can list, create, update, or delete centres.
- Non-admin roles interact with their own centre via `session('centre_id')` and never receive a list of all centres.

**Future auditors**: Do not flag the absence of `CentreScope` on the `Centre` model as a gap. It is intentional and architecturally correct.

---

## Coverage Summary

| Mechanism | Models | Tables |
|-----------|--------|--------|
| CentreScope (direct `centre_id`) | 23 | trainees, activities, assets, users, staffs, supervisors, teachers, ajks, volunteers, admins, letters, letter_templates, activity_logs, activity_schedule_templates, staff_attendances, centre_statistics, centre_audit_logs, asset_parents, assets_enhanced, asset_locations, events, classes, message_templates |
| `centre_isolation` closure (via asset join) | 2 | asset_maintenance, asset_movements |
| Controller-level isolation (no DB scope) | 1 | messages |
| Intentional no-scope (root tenant entity) | 1 | centres |
| **Total covered** | **27** | |

---

## Audit Trail

| Date | Action | Author |
|------|--------|--------|
| 2026-04-01 | Applied CentreScope to 23 models with direct centre_id column | Security hardening session |
| 2026-04-17 | Documented Message and Centre exceptions | Security residuals session |
| 2026-04-25 | Added centre_isolation closure scope to AssetMaintenance and AssetMovement (no centre_id column — isolation via asset join). Restored asset_movements table in test DB. Coverage now 25 scoped models across two mechanisms. | CentreScope gap closure session |
