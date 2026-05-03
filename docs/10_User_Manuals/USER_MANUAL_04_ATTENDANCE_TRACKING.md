# CREAMS User Manual — Attendance Tracking

**Version**: 2.0 (re-baselined 2 May 2026)
**Audience**: PPDK staff and IIUM committee
**Supersedes**: Version 1.0 (deprecated — referenced "Mobile Attendance" and "Biometric Integration" sections that are not implemented in the current system)

---

## 1. Two attendance systems

CREAMS tracks two independent kinds of attendance:

| System | What it tracks | Stored in | Marked by |
|---|---|---|---|
| **Staff attendance** | Daily check-in for staff at their centre | `staff_attendances` table | Staff member themselves; Supervisor can mark on their behalf |
| **Session attendance** | Trainee presence at a specific activity session | `session_attendance` table | Teacher running the session; Supervisor or Admin can mark or correct |

Use the right one — they answer different questions ("did the staff show up to work?" vs "did the trainee attend therapy?").

---

## 2. Per-role permissions

| Action | Admin | Supervisor | Teacher | AJK |
|---|---|---|---|---|
| Mark own staff attendance | Yes | Yes | Yes | Yes |
| Mark another staff's attendance | Yes | Yes (own centre) | No | No |
| Approve staff leave | Yes | Yes (own centre) | No | No |
| Mark trainee session attendance | Yes | Yes (own centre) | Yes (own sessions) | No |
| View attendance reports | All centres | Own centre | Own activities | Own centre |
| Export attendance reports | Yes | Yes | No | No |

---

## 3. Staff attendance

### Schema (the `staff_attendances` table)

| Field | Type | Notes |
|---|---|---|
| `user_id` | bigint | The staff member |
| `centre_id` | string | Centre code |
| `attendance_date` | date | One row per day per staff |
| `check_in_time` | time | When the staff checked in |
| `status` | enum | `present`, `absent`, `leave`, `late` |
| `leave_type` | string | `medical`, `annual`, `emergency`, etc. (when `status = leave`) |
| `approved` | boolean | True if a supervisor/admin approved a leave |
| `approved_by` | bigint | FK to the approver |
| `approved_at` | timestamp | When approved |
| `marked_by_user_id` | bigint | Who marked the attendance row |
| `remarks` | text | Optional notes |

### Common workflows

**Mark your own check-in:**

1. Go to `/staff-attendance` (top nav: Attendance → Staff Attendance) or your dashboard's Quick Actions.
2. The page detects whether you have already checked in today; if not, click **Check In**.
3. Status defaults to `present`. Check-in time is the current server time.
4. The row is created with `marked_by_user_id = your_id`.

**Mark a leave (planned absence):**

1. Same page, click **Apply for Leave**.
2. Pick the date(s), the leave type, and optional remarks.
3. The row is created with `status = leave`, `approved = false`.
4. Your supervisor sees pending leave requests in their dashboard and approves/rejects them.

**Supervisor marks a team member:**

1. Supervisor goes to `/staff-attendance` for the centre.
2. Select the staff member from the centre roster.
3. Mark `present`, `absent`, or `late` for the date.
4. The row is created with `marked_by_user_id = supervisor_id`.

---

## 4. Session attendance (trainee)

### Schema (the `session_attendance` table)

| Field | Type | Notes |
|---|---|---|
| `session_id` | bigint | FK to a specific session of an activity |
| `trainee_id` | bigint | The trainee |
| `attendance_status` | enum | `present`, `absent`, `excused`, `late` |
| `check_in_time` | time | Optional |
| `check_out_time` | time | Optional |
| `notes` | text | Optional, e.g. "left early due to illness" |
| `marked_by` | bigint | Staff member who marked it |

### Common workflows

**Teacher marks attendance for a session:**

1. Open the activity (`/activities/{id}`).
2. Click **Sessions** tab and pick today's session, OR navigate to `/activity-attendance/session/{id}/form`.
3. The form lists all enrolled trainees with status pickers.
4. Mark each trainee `present`, `absent`, `excused`, or `late`.
5. Optionally add notes (e.g., reason for absence).
6. Click **Save Attendance**. The page confirms and stores one row per trainee per session.

**Supervisor / Admin corrects past attendance:**

Same form. Re-marking overwrites the existing row (one row per `(session_id, trainee_id)`).

**Bulk mark attendance:**

The form allows marking all trainees as `present` with a single click, then individually changing exceptions. This is the fastest workflow for a normal session.

---

## 5. Reports

| Report | Path | Roles |
|---|---|---|
| Today's attendance summary (per centre) | `/activity-attendance/stats/today` | Admin, Supervisor |
| Attendance by trainee (history) | `/attendance/trainee/{id}` | Admin, Supervisor, Teacher (own) |
| Attendance export (CSV) | `/activity-attendance/export` (POST) | Admin, Supervisor |
| Centre attendance analytics | `/centre/attendance/analytics` | Admin, Supervisor |

The dashboard's "Today's Attendance %" tile pulls from these endpoints.

---

## 6. What is NOT implemented (despite older docs claiming otherwise)

- **Mobile-specific attendance app** — the web UI is responsive but there is no native mobile app or biometric integration.
- **Biometric integration** — no fingerprint or face-recognition hooks exist.
- **Geofence-based check-in** — no GPS validation on check-in.
- **Self-service attendance correction by trainees** — only staff can mark or correct attendance.

These are valid future-feature requests, not current capabilities.

---

## 7. Edge cases and conventions

- **One row per staff per day.** If you check in twice, the second click is a no-op (you cannot create duplicate rows for the same date).
- **Trainee with no attendance row** for a session = treated as "not yet marked", not "absent". Reports distinguish.
- **Late check-in threshold**: configurable, defaults to 15 minutes after the activity's scheduled start.
- **Excused absences**: count as absent for the percentage stat but not as a "no-show" for the trainee's discipline record.

---

## 8. Troubleshooting

| Symptom | Likely cause | What to do |
|---|---|---|
| Cannot find the staff attendance page | Demo URL needs the `/creams/{demo_id}/` prefix; local does not | Local: `/staff-attendance`. Demo: `/creams/uat/staff-attendance` |
| Trainees not appearing in the session form | They may not be enrolled in that activity | Open the activity → Enrolments tab → confirm; or enrol them first |
| "Already checked in today" error | A row already exists for `(user_id, today)` | View today's record. Edit the existing row instead of creating a new one |
| Attendance percentage shows `0%` despite attendance | Cache may be stale; or the date range is wrong | Refresh the page; check the date filter |
| Cannot approve a leave request | Only supervisors and admins for that centre can approve | Confirm role and centre with admin |

---

*Updated: 2 May 2026 — sprint Day 4*
*Version: 2.0*
*Source of truth: `staff_attendances` and `session_attendance` schemas, `app/Http/Controllers/Activity/AttendanceController.php`, `app/Http/Controllers/Centre/AttendanceController.php`, `StaffAttendanceController`*
