# CREAMS User Manual — Activities Management

**Version**: 2.0 (re-baselined 2 May 2026)
**Audience**: PPDK staff and IIUM committee
**Supersedes**: Version 1.0 (deprecated — listed wrong activity categories: claimed Therapy/Education/Recreation/Life Skills, the actual categories are six disability-support categories from the schema)

---

## 1. What this module does

The Activities module manages the rehabilitation programmes a centre runs. Each Activity is a recurring programme with its own category, schedule, instructor, and trainee enrolment. Sessions are individual instances of an activity at a specific date and time.

---

## 2. Six activity categories

CREAMS uses six disability-support categories defined in the schema. These are enums on the `activities.category` column:

1. Autism Spectrum Support
2. Hearing Impairment
3. Visual Impairment
4. Physical Disabilities
5. Learning Support
6. Speech Therapy

Earlier documentation listed "Therapy / Education / Recreation / Life Skills" — that is incorrect for the current schema. The category enum is enforced at the database level.

---

## 3. Per-role permissions

| Action | Admin | Supervisor | Teacher | AJK |
|---|---|---|---|---|
| View activities list (centre-scoped) | All centres | Own centre | Own centre (assigned) | Own centre (read) |
| View activity details | Yes | Yes | Yes (assigned) | Yes |
| Create new activity | Yes | Yes (own centre) | No | No |
| Edit activity | Yes | Yes (own centre) | Limited | No |
| Delete activity | Yes | No | No | No |
| Assign instructor | Yes | Yes (own centre) | No | No |
| Enrol trainee in activity | Yes | Yes (own centre) | Yes (own activities) | No |
| Manage sessions | Yes | Yes (own centre) | Yes (own activities) | No |
| View activity reports | Yes | Yes (own centre) | Limited | View only |

---

## 4. Activity record fields

The `activities` table schema:

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | Primary key |
| `activity_name` | string | Required, e.g. "Sensory Play Workshop" |
| `activity_description` | text | Optional |
| `category` | enum | One of the 6 listed above |
| `centre_id` | string | Required, links to a centre |
| `duration_weeks` | integer | How many weeks the programme runs |
| `sessions_per_week` | integer | E.g. 2 |
| `session_duration_minutes` | integer | E.g. 60 |
| `max_participants` | integer | Capacity cap |
| `learning_outcomes` | json | Optional structured list of goals |
| `activity_location` | string | Hall name, room number, etc. |
| `instructor_id` | bigint | FK to staffs (User) — assigned teacher |
| `is_active` | boolean | True = currently offered |
| `deleted_at` | timestamp | Soft delete |

Sessions are stored separately in `sessions` and `activity_sessions`-related tables. Enrolments live in `activity_enrollments`.

---

## 5. Common workflows

### View activities

1. Click **Activities** in the main navigation, or go to `/activities`.
2. The list shows activities in your centre (admins see all).
3. Filters: by category, by instructor, by status (active/inactive), search by name.
4. Click an activity row to view details, sessions, and enrolled trainees.

### Create a new activity (Admin or Supervisor)

1. Go to **Activities → Add new** (or `/activities/create`).
2. Fill in:
   - Name, description
   - Category (pick from the 6 enum values)
   - Centre (auto-set if you are a supervisor)
   - Duration (weeks), sessions per week, session length (minutes)
   - Max participants
   - Activity location
   - Instructor (select from staff in the centre)
   - Optional: learning outcomes (free-text or structured)
3. Click **Submit**. The new activity appears in the list.

### Schedule sessions for an activity

Each activity can generate sessions automatically based on `duration_weeks` and `sessions_per_week`, or you can add them one by one:

1. Open the activity.
2. Click **Sessions** tab.
3. Either:
   - Click **Generate Schedule** to auto-create sessions across the duration window (with a chosen start date and weekday/time pattern), OR
   - Click **Add Session** to add a single session at a specific date/time.
4. Each session can later have attendance marked against it (see Manual #4).

### Enrol a trainee in an activity

1. Open the activity.
2. Click **Enrolments** tab.
3. Click **Add trainee**.
4. Search for the trainee (centre-scoped — non-admins only see their own centre).
5. Select and confirm.

The trainee's record now shows the activity in their profile, and the activity's enrolment count increments.

### Edit or deactivate an activity

1. Open the activity.
2. Click **Edit**.
3. Modify fields.
4. To **deactivate** (rather than delete), uncheck `is_active`. The activity stays in the database for historical reporting but no new enrolments can be added.
5. To **delete** (soft-delete, admin only), click **Delete**. The record stays in the database with `deleted_at` set; `withTrashed()` retrieves it.

---

## 6. Schedule templates

CREAMS supports schedule templates (`activity_schedule_templates` table) so you can save a common scheduling pattern (e.g., "Mon/Wed 10am–11am, 12 weeks") and reuse it when creating new activities. Templates are centre-scoped.

This feature is partially exposed in the UI — the demo path uses manual scheduling rather than templates.

---

## 7. Reporting

The module surfaces these reports from the activities data:

- **Enrolment summary per activity**: enrolled / max / waiting list
- **Attendance percentage per session** (links to attendance module)
- **Instructor load**: how many activities each teacher is assigned to
- **Centre-wide summary**: count by category for the dashboard stat tile

Detailed reports require admin or supervisor role.

---

## 8. Troubleshooting

| Symptom | Likely cause | What to do |
|---|---|---|
| "No activities found" | Centre isolation filtering, or no activities seeded for your centre | Confirm centre assignment with admin. On UAT, run UATSeeder. |
| Cannot select an instructor | The dropdown only lists staff in your centre with `role = teacher` | Verify a teacher exists for the centre. |
| Cannot enrol a trainee | Trainee may be inactive, or already enrolled, or your role lacks permission | Check trainee status; check your role permissions. |
| Schedule generation produces 0 sessions | `duration_weeks` × `sessions_per_week` resolved to 0, or start date is in the past | Check inputs. Schedule generator requires a future start date. |
| Activity disappears from list after delete | Soft-delete worked. Use `Activity::withTrashed()` to retrieve. | Admin can restore via tinker. |
| Category enum rejected | The enum has 6 fixed values (see section 2). Database refuses anything else. | Pick from the dropdown — do not type custom values. |

---

*Updated: 2 May 2026 — sprint Day 4*
*Version: 2.0*
*Source of truth: activities table schema, `app/Models/Activity.php`, `app/Http/Controllers/Activity/ActivityController.php`, migration `2025_09_28_164108_drop_activity_categories_and_add_category_to_activities.php`*
