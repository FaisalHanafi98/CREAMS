# CREAMS User Manual — Authentication & Login

**Version**: 2.0 (re-baselined 2 May 2026)
**Audience**: PPDK rehabilitation centre staff and IIUM committee stakeholders
**Supersedes**: Version 1.0 (deprecated — contained drift on roles, password policy, IIUM ID format, session timeout, lockout mechanism, and forgot-password flow)

---

## 1. Quick reference

| Item | Value |
|---|---|
| Login URL (local development) | `http://localhost:8000/auth/login` |
| Login URL (staging/non-production compatibility) | `https://<host>/creams/<demo_id>/auth/login` |
| Login URL (official live production UX) | Clean direct production URLs such as `https://pdk-creams.org/login` without visible `demo`, `uat`, `staging`, or `/creams/<demo_id>/` markers |
| Identifier | Either your registered email address OR your IIUM ID |
| IIUM ID format | 8 characters: 4 letters followed by 4 numbers (example: `ABCD1234`) |
| Password minimum (new accounts) | 12 characters |
| Session timeout | 8 hours of inactivity |
| Failed login limit | 5 attempts per minute per identifier+IP, then HTTP 429 for the rest of that minute |
| Forgot password | Self-serve at `/forgot-password` (rate limited to 3 requests per minute, 10 per hour) |

---

## 2. User roles

CREAMS currently supports **four active roles**:

| Role | Purpose |
|---|---|
| **Admin** | Cross-centre system administration. Sees all centres. Creates and manages user accounts. |
| **Supervisor** | Centre-level oversight. Manages activities, teachers, and trainee records within their assigned centre. |
| **Teacher** | Activity instruction. Views assigned trainees and activities, marks attendance, records progress. |
| **AJK** | Committee member with read-and-limited-write access to their assigned centre. |

Two additional roles are **planned but not yet active**: Trainee (self-service portal) and Parent (guardian portal). These appear in some seed data but do not have working dashboards as of this manual version.

---

## 3. Logging in

### Step 1 — Open the login page

In a modern browser (Chrome, Firefox, Edge, Safari), navigate to the login URL above.

### Step 2 — Enter your identifier

The login form has **one identifier field** that accepts either:

- Your **email address**, e.g. `firstname.lastname@example.com`
- Your **IIUM ID**, e.g. `ABCD1234` (4 letters then 4 numbers)

You do not need to indicate which type you are entering. The system detects the format automatically (email contains `@`, IIUM ID does not).

### Step 3 — Enter your password

Passwords are case-sensitive. Caps Lock will affect the password.

### Step 4 — (Optional) Tick "Remember me"

If checked, your session persists in the browser even after you close it. Only use on personal devices.

### Step 5 — Click "Login"

You will be redirected to the dashboard appropriate to your role:

- Admin → `/admin/dashboard`
- Supervisor → `/supervisor/dashboard`
- Teacher → `/teacher/dashboard`
- AJK → `/ajk/dashboard`

If your credentials are valid you will land on the dashboard. If not, you stay on the login page with an error.

---

## 4. Failed login behaviour

CREAMS uses **rate limiting**, not account lockout. The distinction matters:

- The system allows **5 login attempts per minute** for any single combination of identifier and IP address.
- The 6th attempt within the same minute returns HTTP 429 and a "Too many login attempts" message.
- The next minute, attempts are allowed again (rolling window).
- **Your account is not locked.** No administrator action is required to "unlock" you. Wait one minute and try again.

This protects against password-guessing attacks without the operational burden of admin-managed unlocks.

---

## 5. Forgot your password

CREAMS supports **self-serve password reset**.

### Step 1 — Click "Forgot Password" on the login page

Or navigate directly to `/forgot-password`.

### Step 2 — Enter your registered email address

The system sends a password reset link to that email if an account exists. For security, the system does not tell you whether or not the email matched a real account — you receive the same response either way.

### Step 3 — Open the email and click the reset link

The link contains a single-use token valid for 60 minutes.

### Step 4 — Set a new password

Your new password must be at least 12 characters. Strong passwords mix uppercase, lowercase, numbers, and symbols.

Reset requests are rate-limited (3 per minute, 10 per hour per IP) to prevent abuse. If you exceed the limit, wait and retry.

If you do not receive the email after 5 minutes:
- Check your spam folder
- Verify the email address matches what is on your account (contact your administrator if unsure)
- Try again after 1 minute (rate limit window)

---

## 6. Session behaviour

- A successful login creates a session that lasts **8 hours of inactivity** (`SESSION_LIFETIME=480` minutes).
- The session cookie is regenerated on login to prevent session fixation attacks.
- There is no countdown warning before timeout — if your session expires while you have a page open, the next click sends you to the login screen.
- Logging out clears the session immediately.

To log out, click your name in the top navigation, then "Logout".

---

## 7. Creating new accounts

Only **Admin** users can create accounts.

### Admin workflow

1. Log in as Admin.
2. Navigate to user management (admin dashboard → "Users").
3. Click "Add new user".
4. Fill in:
   - **Name**: required
   - **Email**: required, must be unique
   - **IIUM ID**: required if applicable, must be unique, format `ABCD1234`
   - **Phone**: optional
   - **Role**: select from Admin, Supervisor, Teacher, AJK
   - **Centre**: select the centre the user belongs to
   - **Password**: minimum 12 characters
   - **Status**: usually `active`
5. Submit.

The new user receives no automatic welcome email in the current version — communicate the credentials to them by another channel.

---

## 8. Multi-centre data isolation

CREAMS enforces centre-based data isolation automatically. After login:

- Your session stores your assigned `centre_id`.
- Most database queries are filtered to your centre — you cannot see other centres' trainees, activities, staff, etc.
- **Admin role is the exception**: admins see data across all centres for cross-centre management.

This is enforced at the database query layer (not the UI layer) via the `CentreScope` mechanism. See `docs/MULTI_CENTRE_ISOLATION.md` for the security architecture.

---

## 9. Troubleshooting

| Symptom | Likely cause | What to do |
|---|---|---|
| "Invalid credentials" error | Wrong password, wrong identifier, account doesn't exist, or account `status != 'active'` | Verify the identifier and password. Contact admin to confirm the account exists and is active. |
| "Too many login attempts" (429) | More than 5 login attempts in the past minute from your IP+identifier | Wait one minute. Verify the password before retrying. |
| Login succeeds but dashboard shows no data | Your account may not have a centre assigned, or your role does not have permissions for that page | Check with admin that your account has `centre_id` set and the correct `role`. |
| Forgot-password email not arriving | Check spam folder. Email may not be configured on the local environment. | On local development, check `storage/logs/laravel.log` — the email content is often logged there if no SMTP is configured. |
| Page shows "Session expired" | 8 hours of inactivity passed | Log in again. |

---

## 10. Security notes

- **Custom session-based authentication.** CREAMS does not use Laravel Breeze, Sanctum, or JWT. The login flow is `POST /auth/check` handled by `MainController@check`.
- **PDPA compliance.** This system handles real trainee personal data including IC numbers. Centre staff must follow PDPA guidelines on data handling, access, and disclosure.
- **No real data on shared environments.** Staging, UAT, and demo environments use only `UATSeeder` (anonymised data). Real trainee records exist only on production and authorised local development environments.

---

## 11. For the live demo

Stakeholders attending the demo can use these credentials on the local instance:

| Role | Identifier | Password |
|---|---|---|
| Admin (cross-centre) | `super.admin@uat.creams.test` | `UatPass2026!` |
| Supervisor (Centre A) | `supervisor.a1@uat.creams.test` | `UatPass2026!` |
| Teacher (Centre A) | `teacher.a1@uat.creams.test` | `UatPass2026!` |
| AJK (Centre A) | `ajk.a1@uat.creams.test` | `UatPass2026!` |

Centres B and C have analogous accounts (`*.b1`, `*.c1`).

---

*Updated: 2 May 2026 — sprint Day 4*
*Version: 2.0*
*Source of truth: `app/Http/Controllers/MainController.php`, `app/Providers/RouteServiceProvider.php`, `config/session.php`*
