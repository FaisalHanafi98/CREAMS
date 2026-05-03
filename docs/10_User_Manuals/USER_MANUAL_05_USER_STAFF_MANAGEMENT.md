# CREAMS User Manual — User & Staff Management

**Version**: 2.0 (re-baselined 2 May 2026)
**Audience**: Admin and Supervisor users
**Supersedes**: Version 1.0 (deprecated — invented user sub-categories like "System Administrators / Centre Administrators / Data Administrators" that don't exist; described "Performance Tracking" and "Professional Development" modules that aren't implemented)

---

## 1. What this module does

CREAMS user management is built around the `staffs` table (which the `User` model wraps). Every CREAMS user — Admin, Supervisor, Teacher, AJK — is a row in `staffs`. The `role` column distinguishes them.

This module covers:
- Creating new user accounts (admin only)
- Editing user details (admin and supervisor for own centre)
- Assigning role and centre
- Setting and resetting passwords
- Activating, deactivating, soft-deleting accounts
- Browsing the staff directory

It does **not** include performance reviews, professional development tracking, or HR features. Those are not implemented in the current system.

---

## 2. Per-role permissions

| Action | Admin | Supervisor | Teacher | AJK |
|---|---|---|---|---|
| List all users | All centres | Own centre only | No | No |
| View own profile | Yes | Yes | Yes | Yes |
| View other staff profiles | Yes | Yes (own centre) | Limited | Limited |
| Create new user | Yes | No | No | No |
| Edit any user | Yes | Limited (own centre) | No | No |
| Edit own profile | Yes | Yes | Yes | Yes |
| Reset another user's password | Yes | No | No | No |
| Soft-delete user | Yes | No | No | No |
| Assign role | Yes | No | No | No |
| Assign centre | Yes | No | No | No |

---

## 3. The `staffs` table schema

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | Primary key |
| `iium_id` | string(8) | 4 letters + 4 numbers, e.g. `ABCD1234`. Unique. Optional for non-IIUM staff. |
| `name` | string | Required |
| `email` | string | Required, unique |
| `email_verified_at` | timestamp | Set on account creation if pre-verified |
| `password` | string | Auto-hashed via Eloquent cast |
| `phone` | string | Optional |
| `address` | text | Optional |
| `education_level` | string | Optional, e.g. "Bachelor's", "Master's" |
| `education_specialization` | string | Optional |
| `teaching_specialization` | string | Optional, e.g. "Speech Therapy" |
| `date_of_birth` | date | Optional |
| `role` | enum | `admin`, `supervisor`, `teacher`, `ajk` (active); `trainee`, `parent` reserved for future |
| `status` | enum | `active`, `inactive`, `suspended` |
| `centre_id` | string | Required, links to a centre |
| `encrypted_id` | string | URL-safe encrypted version of `id` for routes that need to hide the numeric ID |
| `avatar` | string | Path to profile picture in storage |
| `position` | string | Job title, e.g. "Head Teacher" |
| `about` | text | Bio |
| `centre_location` | string | Free-text location detail (e.g. office room) |
| `last_accessed_at` | timestamp | Updated on every authenticated request |
| `remember_token` | string | "Remember me" token |
| `deleted_at` | timestamp | Soft delete |

---

## 4. Common workflows

### Admin: create a new user

1. Log in as Admin.
2. Navigate to `/admin/users` (top nav: User Management, or Quick Actions on the dashboard).
3. Click **Add new user**.
4. Fill the form:
   - **Name**, **email**: required
   - **IIUM ID**: 4 letters + 4 numbers (e.g. `ABCD1234`). Optional but unique if set.
   - **Phone**: optional
   - **Role**: pick from `admin`, `supervisor`, `teacher`, `ajk`
   - **Centre**: pick from active centres
   - **Password**: minimum 12 characters (system enforces complexity)
   - **Status**: usually `active`
5. Click **Create**.
6. The new user can log in immediately. Communicate the credentials to them out-of-band (no automatic email).

### Supervisor: view team

1. Log in as Supervisor.
2. Navigate to `/supervisor/users` (top nav: My Team).
3. The list is centre-scoped — you only see staff assigned to your centre.
4. Click a row to view the staff profile, schedule, and assigned activities.

### Edit own profile (any role)

1. Click your name in the top-right navigation, then **Profile**.
2. Update editable fields: phone, address, bio, avatar.
3. Some fields (role, centre, IIUM ID, email) are admin-only — you cannot self-modify them.
4. To change your password: enter current password + new password (twice).
5. Click **Save**.

### Admin: reset another user's password

1. Open the user's profile from `/admin/users`.
2. Click **Reset Password**.
3. Set a temporary password (minimum 12 characters).
4. Communicate the new password to the user out-of-band.
5. Encourage them to change it on first login.

### Admin: deactivate or soft-delete a user

- **Deactivate** (`status = inactive`): user remains in the database but cannot log in. Reversible by setting `status = active`.
- **Soft-delete** (`deleted_at` set): user is hidden from all listings but the row remains. Recoverable with `User::withTrashed()->find($id)->restore()` via tinker.

Use deactivate for a temporary leave (e.g. sabbatical). Use soft-delete when the user has left the organisation permanently.

---

## 5. Staff directory search

The user list supports:

- **Free-text search** (matches name, email, IIUM ID)
- **Filter by role**
- **Filter by centre** (admin only — supervisor is auto-filtered to their centre)
- **Filter by status** (active / inactive / suspended)

---

## 6. Centre assignment and isolation

`centre_id` controls what data the user sees for trainees, activities, attendance, etc. Reassigning a user to a different centre means they immediately stop seeing the old centre's data and start seeing the new one's.

There is no built-in workflow for cross-centre staff (one staff member assigned to multiple centres). If that is needed, the workaround is to create a second account on the second centre with the same name. This is a known design constraint.

---

## 7. What is NOT implemented

The previous version of this manual described features that do not exist in code:

- **Performance Tracking module** (KPIs, ratings, reviews) — not implemented
- **Professional Development module** (training records, certifications) — not implemented
- **Multi-centre staff assignment** (one user spanning centres) — not implemented; workaround is duplicate accounts
- **Self-serve role changes** — not allowed (admin-only by design)

These are valid future-feature ideas, not current capabilities.

---

## 8. Troubleshooting

| Symptom | Likely cause | What to do |
|---|---|---|
| New user cannot log in | `status` not set to `active`, OR password not set, OR account `deleted_at` is non-null | Open the user record in `/admin/users` and verify status. Reset password if needed. |
| User created but does not appear in supervisor's team list | Wrong `centre_id` assigned | Re-check the centre assignment. Supervisor only sees their own centre. |
| Cannot change a user's role | Only admin can change role | Confirm you are logged in as admin. |
| IIUM ID rejected as duplicate | Another user already has this `iium_id` | Search the existing record; if it is a real duplicate, contact the admin. |
| Email validation rejects a valid-looking email | Email already in `staffs` (uniqueness), or the format failed Laravel's `email` rule | Check uniqueness. Try without trailing whitespace. |

---

*Updated: 2 May 2026 — sprint Day 4*
*Version: 2.0*
*Source of truth: `staffs` table schema, `app/Models/User.php`, `app/Http/Controllers/AdminController.php`, `app/Http/Controllers/Staff/*`*
