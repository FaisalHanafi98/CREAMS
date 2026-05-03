# CREAMS User Manual — Dashboards (All Roles)

**Version**: 2.0 (re-baselined 2 May 2026)
**Audience**: PPDK staff and IIUM committee
**Supersedes**: Version 1.0 (deprecated — claimed a Trainee dashboard that does not exist, listed wrong activity categories, described a System Health Monitoring section that is not in the live app)

---

## 1. Which dashboard you see

After login, CREAMS routes you to a role-specific dashboard:

| Role | URL | Scope of data shown |
|---|---|---|
| Admin | `/admin/dashboard` | All centres |
| Supervisor | `/supervisor/dashboard` | Your assigned centre only |
| Teacher | `/teacher/dashboard` | Your assigned centre only |
| AJK | `/ajk/dashboard` | Your assigned centre only |

Trainee and Parent dashboards are **planned but not yet implemented**. Staff with `role = trainee` or `role = parent` cannot currently log in to a working dashboard.

---

## 2. Common dashboard layout

Every active dashboard has the same overall structure. The differences are in the data shown and the actions available.

```
+------------------------------------------------------------+
| Top navigation: logo · main menu · profile · logout        |
+------------------------------------------------------------+
| Stats row: counters for key entities (4 tiles)             |
+----------------------+-------------------------------------+
| Recent Activities    | My Schedule                         |
+----------------------+-------------------------------------+
| Quick Actions panel  | Notification panel                  |
+------------------------------------------------------------+
```

### Stats row

The four counter tiles depend on your role. They show whole numbers and a percentage where applicable. A stat showing `0` means "no records found", not "system error".

| Tile | Admin sees | Supervisor / Teacher / AJK sees |
|---|---|---|
| Tile 1 | Total active staff (all centres) | Active staff in your centre |
| Tile 2 | Total active trainees (all centres) | Active trainees in your centre |
| Tile 3 | Active activities (all centres) | Active activities in your centre |
| Tile 4 | Today's attendance % | Today's attendance % for your centre |

### Recent Activities feed

Lists the most recent activity log entries within your data scope (centre-scoped for non-admin). Each entry has a timestamp and the actor who performed it. If empty, the panel shows "No Recent Activities".

### My Schedule

Shows the activities and sessions scheduled for today and the next few days, scoped to your role:
- Admin and Supervisor: all activities in scope
- Teacher: activities you are assigned to instruct
- AJK: activities at your centre

If nothing is scheduled, you see "All Clear! 🎉".

### Quick Actions panel

Role-dependent shortcuts to common operations. Hover for tooltips. Examples:
- Admin: Add User, Create Centre, Generate Report
- Supervisor: Add Trainee, Schedule Activity, View Attendance
- Teacher: Mark Attendance, View Trainees
- AJK: View Activities, View Trainees

Exact buttons may differ between releases — they are configured in `resources/views/dashboard/`.

### Notification panel / Notification Center

Shows unread notifications for your account: new trainee enrolments, schedule changes, system messages from admin. Click a notification to mark it read and navigate to the relevant page.

---

## 3. Per-role differences

### Admin dashboard

- Stats are **system-wide** (all centres summed).
- Has access to **all** quick actions.
- Recent Activities feed is unfiltered — sees actions from every centre.

### Supervisor dashboard

- Stats are **centre-scoped** to your assigned `centre_id`.
- Quick actions exclude system-admin functions (no centre creation, no system settings).
- Recent Activities show only your centre's events.

### Teacher dashboard

- Stats are centre-scoped.
- Quick actions focus on day-to-day teaching: attendance, trainee progress, activity views.
- Cannot create users or modify centre settings.

### AJK dashboard

- Stats are centre-scoped.
- Mostly read-oriented quick actions: viewing activities, trainees, schedules.
- AJKs are committee members rather than operational staff, so write permissions are intentionally limited.

---

## 4. Activity categories (reference)

The dashboard "Activities" stat counts records across these six categories (defined in the migration `2025_09_28_164108_drop_activity_categories_and_add_category_to_activities.php`):

1. Autism Spectrum Support
2. Hearing Impairment
3. Visual Impairment
4. Physical Disabilities
5. Learning Support
6. Speech Therapy

Earlier documentation referenced "Therapy / Education / Recreation / Life Skills" as categories — that is incorrect for the current schema.

---

## 5. Returning to the dashboard

From any page:

- Click the CREAMS logo in the top-left, OR
- Click "Dashboard" in the main navigation, OR
- Navigate directly to `/<role>/dashboard`

---

## 6. Troubleshooting

| Symptom | Likely cause | What to do |
|---|---|---|
| All stats show `0` | The current centre has no seeded or real data | If on UAT, run `php artisan db:seed --class=UATSeeder`. If on live data, verify with admin that records exist for the centre. |
| "No Recent Activities" persists despite activity | Activity log scope filtered to your centre/role and there is nothing in it | This is normal in a quiet centre. Try as admin to see if the underlying data exists. |
| Dashboard loads slowly | Likely a query performance issue | Check `storage/logs/laravel.log` for slow query warnings. Cached statistics are computed on demand. |
| 403 or 404 when visiting `/<role>/dashboard` | Your role does not match the URL path, OR you are not authenticated | Re-check your session and your role assignment. |
| Stats don't match what you expect | Centre isolation is working — you are seeing only your centre's data | Confirm with admin that this matches your assigned `centre_id`. |

---

*Updated: 2 May 2026 — sprint Day 4*
*Version: 2.0*
*Source of truth: `app/Http/Controllers/Dashboard/DashboardController.php`, live HTML for `/admin/dashboard`, `/supervisor/dashboard`, `/teacher/dashboard`, `/ajk/dashboard`*
