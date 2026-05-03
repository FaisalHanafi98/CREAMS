# CREAMS — Live Demo Script

**Date prepared**: 3 May 2026 (sprint Day 5)
**Demo target date**: Day 7 of the sprint
**Audience**: 2 PPDK rehabilitation centre staff (operational, less tech-literate) + 2 IIUM committee members (academic / governance focus)
**Format**: Live walkthrough on local instance (`http://localhost:8000`), single presenter (Faisal), ~45 minutes including Q&A
**Environment**: localhost, fresh `migrate:fresh --seeder=UATSeeder`, anonymised UAT data only — no real PII shown

---

## 1. Pre-demo checklist (do this 30 minutes before)

Run through this list before the audience arrives:

- [ ] `git status` shows clean working tree
- [ ] `php artisan test` passes (target: 359/359)
- [ ] `php artisan migrate:fresh --seeder=UATSeeder --force` runs without error
- [ ] `php artisan serve` running on port 8000
- [ ] Browser open at `http://localhost:8000/auth/login`, no other tabs
- [ ] Browser zoom set to 110–125% (better readability for the room)
- [ ] Notification panel hidden in the OS (no incoming chat alerts during the demo)
- [ ] `tail -f storage/logs/laravel.log` open in a side terminal (visible only to you, useful if something breaks)
- [ ] Backup tab: open `docs/audit/UAT_BLOCKERS_2026-04-30.md` and `docs/MULTI_CENTRE_ISOLATION.md` so you can pull them up if asked
- [ ] Print or pull up `docs/10_User_Manuals/USER_MANUALS_MASTER_INDEX.md` for handover at the end
- [ ] Confirm room setup: projector tested, you can see the laptop screen and the projection at the same time

---

## 2. Opening (3 minutes — both audiences)

**Goal**: Set context. Tell them what they're about to see, why it matters, and how the next 45 minutes will flow.

**Say:**

> "Good morning. Thanks for coming. Today I'm going to walk you through CREAMS, the rehabilitation centre management system, in three parts:
>
> First, the day-to-day flows for centre staff: how a teacher marks attendance, how a supervisor manages activities, what the admin sees across all centres. That's the part most relevant to the PPDK staff in the room.
>
> Second, the architecture and governance — how the system keeps each centre's data separate, how PDPA-protected information is handled, and how every change is audited. That's more relevant to the IIUM committee.
>
> Third, where we are today versus what's planned, and what comes next.
>
> Everything I'll show today runs on a clean test database with completely fictional data. No real trainee names, no real ICs, no real centre information. So if anyone takes a screenshot, you're not capturing PDPA-protected content."

**Don't:**
- Apologise for the system being a localhost demo
- Spend more than 3 minutes here

---

## 3. Part A — Operational walkthrough (15 minutes — primarily for PPDK staff)

### Beat A1: Login as Admin (2 min)

**URL**: `http://localhost:8000/auth/login`
**Credentials**: `super.admin@uat.creams.test` / `UatPass2026!`

**Show:**
- Single login form, one identifier field that accepts email or IIUM ID
- Type the email, paste the password
- Click Login → redirect to `/admin/dashboard`

**Say:**
> "The system supports four roles: Admin, Supervisor, Teacher, AJK. The login form accepts either an email address or an IIUM ID — it auto-detects the format. Each role lands on a different dashboard."

**Watch for:**
- 302 redirect happens fast (under 1 second)
- Dashboard renders without 5xx
- Top nav shows "UAT Super Admin" name

### Beat A2: Admin dashboard tour (3 min)

**Show:**
- Stats row: 16 staff, 21 trainees, 9 activities (across all 3 UAT centres)
- Recent Activities feed (may be empty on a fresh seed — say so honestly)
- "My Schedule" section showing today's activities
- Quick Actions panel
- Notification center

**Say:**
> "As Admin I can see across all centres. The four tiles at the top: total active staff, total active trainees, total active activities, today's attendance percentage. Below that is recent system activity, my schedule for today, and quick actions like adding a user or creating an activity."

**Pre-empt:**
> "On a real production deployment with real data, these tiles show actual numbers and the recent activities feed shows what staff have been doing. On this fresh test seed, you'll see small numbers — that's expected."

### Beat A3: Trainee management (4 min)

**URL**: click **Trainees** in nav, or go to `/trainees`

**Show:**
- List of 21 UAT trainees across 3 centres
- Filter by centre — pick "UAT Centre A" → list narrows to 7 trainees
- Click one trainee → trainee profile loads
- Walk through the profile sections: identity, centre, condition, guardian, emergency contact, consent flags
- Highlight the PDPA consent section: photo / services / data — all required to register

**Say:**
> "Each trainee record holds personal details, the assigned centre, the disability category, guardian and emergency contact information, and three explicit PDPA consent flags. A trainee cannot be registered without all three consents being ticked — that's enforced at the database validation layer."

**For IIUM committee:**
> "Notice the centre filter at the top — supervisors and teachers don't see this filter because they're locked to their assigned centre by the database scope. We'll come back to this in the architecture section."

### Beat A4: Activities (3 min)

**URL**: click **Activities** in nav, or go to `/activities`

**Show:**
- List of 9 activities across the 3 centres
- Filter by category — show the 6 disability-support categories (Autism Spectrum Support, Hearing Impairment, Visual Impairment, Physical Disabilities, Learning Support, Speech Therapy)
- Click an activity → activity detail with sessions tab and enrolments tab

**Say:**
> "Each activity has a category from the six disability-support categories defined in the schema. An activity belongs to one centre, has an instructor, runs for a number of weeks with a fixed number of sessions per week. Trainees enrol in activities, and attendance is marked per session."

**Don't:**
- Click into the schedule generation flow today (it's stable but adds a beat that takes 2-3 minutes — save for Q&A if asked)

### Beat A5: Attendance (3 min)

**URL**: click **Attendance** in nav, or go to `/staff-attendance` then `/activity-attendance`

**Show:**
- Staff attendance page: where staff check themselves in
- Trainee session attendance form (pick any session) — a list of enrolled trainees with status pickers (present / absent / excused / late)

**Say:**
> "Two attendance systems. Staff attendance is daily — every staff member checks in at the start of their day. Trainee session attendance is per-session — a teacher marks each enrolled trainee for a specific activity session. Both feed the dashboard's 'today's attendance' percentage and the various attendance reports."

---

## 4. Part B — Architecture and governance (12 minutes — primarily for IIUM committee)

### Beat B1: Centre isolation in action (4 min)

**Show:**
- Open a second browser tab (or use Chrome incognito)
- Log in as Supervisor for Centre A: `supervisor.a1@uat.creams.test` / `UatPass2026!`
- Show that this supervisor sees only Centre A's trainees (7), not the 21 admin sees
- Activities list also narrowed to Centre A's 3 activities
- Open the URL `http://localhost:8000/trainees` directly — still scoped, can't escape via URL crafting

**Say:**
> "This is multi-centre data isolation. The supervisor account is bound to Centre A. They cannot see Centre B's or Centre C's trainees, activities, attendance, or staff — even if they craft a direct URL. The isolation is enforced at the database query layer through a scope mechanism called CentreScope, applied to 23 models. Two more models — asset maintenance and asset movement — use a closure-based scope because they relate to centre data through an asset relationship rather than a direct centre_id column. Total: 25 models with isolation."

**For deeper questions:**
- "How is this implemented?" → "A global query scope on the Eloquent model. Every query for these models silently appends a WHERE centre_id = session_centre_id clause."
- "Can it be bypassed?" → "Only by an admin, who has a session attribute that disables the scope. And only inside the application — there's no UI surface to disable it."
- "What if someone shares a URL with a trainee ID across centres?" → "They get a 404. The model query returns no row because the scope filtered it out."

### Beat B2: Audit trail (3 min)

**Show:**
- Switch back to admin tab
- Open tinker in side terminal: `php artisan tinker`
- Run: `App\Models\TraineeAuditLog::latest()->take(5)->get()`
- Show some recent audit entries (you may need to edit a trainee first to generate one)

**Say:**
> "Every meaningful change to a trainee record is logged. We capture: who did it (user_id), what action (create / update / delete), the old and new values as JSON, the IP address, and the timestamp. Same for centre-level changes (centre_audit_logs) and activity changes (activity_logs). This audit trail is permanent — even soft-deleting a trainee leaves the record in the database for recovery and audit purposes."

**For questions:**
- "Is the audit log itself audited?" → "It's append-only by convention. There's no UI to delete log entries. An attacker with database access could tamper, but at that point all bets are off."
- "Can the audit log be exported?" → "Currently via tinker / direct SQL. A UI export is on the planned-features list."

### Beat B3: Security posture (3 min)

**Say (no clicking needed, this is verbal):**

> "The security posture today:
>
> - Custom session-based authentication. Login goes through a single endpoint that auto-detects whether the identifier is an email or an IIUM ID. Passwords are bcrypt-hashed. Sessions regenerate on login to prevent fixation attacks.
> - Rate limiting on every authentication endpoint: 5 login attempts per minute per identifier and IP, 3 forgot-password requests per minute, 3 registrations per minute.
> - CSRF protection on every state-changing request via Laravel's built-in middleware.
> - Centre-based data isolation as we just demonstrated.
> - Soft deletes on critical tables — admins can recover deleted trainees through the database.
> - PDPA grep gate in the git pre-commit hook — blocks Malaysian IC patterns and password literals from entering committed code, with an explicit exemption for the one acknowledged real-data seeder which is hard-gated to local development only.
> - 359 automated tests passing, including dedicated tests for centre isolation and role-based access control.
>
> What is not implemented yet, and worth flagging: multi-factor authentication, IP allowlisting, real-time session monitoring dashboard. Those are valid future hardening items but out of scope for this UAT cycle."

### Beat B4: PDPA discipline (2 min)

**Say:**

> "The system handles real personal data of children with disabilities — among the most sensitive data categories under PDPA. The discipline we follow:
>
> - Real trainee data exists only on production and authorised local development environments.
> - Staging, UAT, and demo environments — including this one — only ever load the UATSeeder which generates everything via Faker with deliberately fake state codes on the IC numbers.
> - The codebase has a documented staging seed policy that prevents the real-data seeder from running anywhere except a developer's local machine. Three independent guards: a code check that throws if APP_ENV is not local, a deployment script that hardcodes the seeder name, and an .env file that the code check reads.
> - Pre-commit hook blocks accidental commit of IC patterns or password literals.
> - There's still historical PDPA exposure in the git history from the older codebase — 72 IC patterns total — that we've documented and parked as a post-delivery cleanup item. We can rewrite the history to remove them, but that's a disruptive operation that's better done after the project is handed off to an operations team."

---

## 5. Part C — Where we are today and what comes next (5 minutes)

### Beat C1: Honest current state (2 min)

**Say:**

> "Where we are today:
>
> - The system runs reliably on local development. 359 tests passing.
> - All 4 active roles work end-to-end: login, dashboard, role-specific data, role-specific permissions.
> - The 8 user manuals you'll receive in the handover package have been re-baselined this week against the actual running system — they're accurate as of today.
> - We have anonymised UAT data that demonstrates every flow without exposing real children's records.
> - Staging deployment is intentionally deferred to the next phase pending a move to a more capable hosting environment.
> - Mobile app, biometric attendance, MFA, automated email distribution, real-time monitoring dashboards — all valid future features, none currently implemented."

### Beat C2: What's next (2 min)

**Say:**

> "What comes next, in order:
>
> - Move the application to a dedicated hosting environment. Today it's local; the next environment will be a properly resourced VPS with backups and monitoring.
> - UAT pass with real centre staff using the system on the staging environment with seed data, so they can sign off on the workflow before it touches real records.
> - Limited rollout: one pilot centre, real users, real data, rollback plan ready.
> - Then phased rollout to additional centres."

### Beat C3: Handover package (1 min)

**Show:**
- The 8 user manuals folder: `docs/10_User_Manuals/`
- The master index
- The audit folder: `docs/audit/`

**Say:**
> "Everything I've shown today is documented. Each of you will receive the handover package — manuals for centre staff, governance docs and the audit baseline for the IIUM committee. Questions are easier when you have the docs in front of you, so please use them."

---

## 6. Q&A guidance (10 minutes reserved)

Anticipated questions and good answers:

| Question | Answer |
|---|---|
| "When can we go live?" | "After UAT sign-off on the staging environment — that's the next phase. Realistic timeline depends on the hosting move and the UAT cycle, but planning for 4–8 weeks." |
| "Who owns this code? Will we have access?" | "The codebase is in git. Handover includes repository access, deployment runbook, and the manuals. The system is yours to operate." |
| "Has it been security-audited externally?" | "Internal security hardening has been done — see the security commits in git and the security tests in tests/Feature/Security. External audit has not been done. That's a recommended pre-production step." |
| "What about Trainee and Parent self-service?" | "Planned. Schema fields exist, role values exist in the seeders, but the dashboards and self-service workflows are not built. That's the next major feature work after rollout." |
| "Will it work on mobile?" | "It's web-responsive. Smartphones can access via browser. There's no native mobile app." |
| "What happens if a centre staff makes a mistake?" | "Soft deletes — trainee records aren't truly deleted, they're hidden from listings but recoverable via admin tooling. Audit log captures the change for review." |
| "How much does it cost to run?" | "Currently zero — it's local. Hosting on a basic VPS, around USD$10–20/month for the box plus domain plus optional managed database. Lower with co-tenancy on existing infrastructure." |
| "What if you (Faisal) aren't available?" | "Documentation is the answer to that. The manuals, the CLAUDE.md governance, the deployment guides, and the source code itself — all designed to be picked up by another developer." |

If a question is technical and you don't know the answer, say "I don't have that off the top of my head — let me get back to you with a written answer." Don't bluff.

---

## 7. Closing (1 minute)

**Say:**

> "Thanks for your time. To summarise: CREAMS handles the day-to-day operations of a rehabilitation centre — trainees, activities, attendance, staff, letters — with strict centre-based data isolation, audit trails, and PDPA discipline. It's stable, tested, and documented. The next step is moving to a hosting environment for proper UAT, then a pilot rollout. The handover package is in your inbox / on this USB drive / via this link. Any final questions?"

---

## 8. Backup plans

| If this happens... | Do this |
|---|---|
| Local app crashes mid-demo | `php artisan serve` again, refresh, continue. ~10 second interruption. The seed data persists. |
| A page returns 500 | Don't panic. Refresh once. If still 500, switch to the next beat and revisit at the end. Show the laravel.log entry to the IIUM committee — turn the bug into a transparency moment. |
| You forget the seeded password | `UatPass2026!` is in `database/seeders/UATSeeder.php` line 34 (the `UAT_PASS` constant). |
| Stakeholder asks something not covered | "Good question — let me note that and respond in writing this week." Don't improvise architecture decisions live. |
| Audience seems lost in Part B (architecture) | Cut to Beat B4 (PDPA) — that's the most accessible architecture beat. Skip B1's deeper technical answers. |
| You're running long | Skip Beat B3 (security posture verbal section) — the manuals cover it. |
| You're running short | Add Beat A4.5: schedule generation for an activity (3 min). |

---

## 9. Post-demo

- Capture any defects raised during the demo into `docs/audit/UAT_DEFECTS_2026-05-XX.md` with severity
- Send a thank-you email with the handover package link
- If any P0/P1 defects surfaced, fix them in the buffer time on Day 7

---

*Created: 3 May 2026 — sprint Day 5*
*Demo date: target Day 7 (5 May 2026)*
*Handover docs: `docs/10_User_Manuals/`, `docs/MULTI_CENTRE_ISOLATION.md`, `docs/audit/UAT_BLOCKERS_2026-04-30.md`, `docs/04_Deployment_Guides/STAGING_SEED_POLICY.md`*
