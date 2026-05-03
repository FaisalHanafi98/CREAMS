# CREAMS — Stakeholder Handover Package

**Date**: 4 May 2026 (sprint Day 6)
**For**: PPDK rehabilitation centre staff, IIUM committee members, future maintainers
**Status**: Hand-off ready for the live demo on Day 7 and post-demo distribution

---

## Start here

This package is the single entry point for everything stakeholders need after the live demo. It is structured so that:

- **PPDK staff** can find day-to-day operational guidance fast
- **IIUM committee** can find governance, security, and architecture references
- **Future developers / operators** can rebuild and run the system from scratch

If you read only one section, read **section 1 (Executive summary)** and then jump to the section labelled for your role.

---

## 1. Executive summary

CREAMS is a Laravel-based web system for managing community-based rehabilitation centres (PPDKs). It supports four roles (Admin, Supervisor, Teacher, AJK) operating across multiple centres with strict centre-based data isolation, audit trails, and PDPA-compliant data handling.

**Current state (4 May 2026):**

- The system runs reliably on local development.
- 359 automated tests pass (520 assertions, 0 failures).
- All 4 active roles work end-to-end — login, dashboard, role-specific data, role-specific permissions.
- Multi-centre isolation is enforced via 23 `CentreScope` models + 2 closure-scoped models.
- Eight user manuals have been re-baselined this week against the actual running system.
- Anonymised UAT data (3 centres, 16 staff, 21 trainees, 9 activities) demonstrates every flow without exposing real records.
- Live demo to mixed audience (2 PPDK + 2 IIUM) is scheduled for Day 7.

**What is intentionally NOT done this sprint:**

- Production deployment — deferred until move to a properly resourced VPS
- Recorded video demo — replaced by live demo (Option C) per stakeholder preference
- New feature work beyond P0/P1 fixes — none surfaced; the system is stable

**Handover effective date**: end of Day 7 (5 May 2026), after the live demo.

---

## 2. For PPDK rehab centre staff

You will be the daily users of CREAMS. Your reading priority:

| # | Document | Why |
|---|---|---|
| 1 | [User Manual 01 — Authentication & Login](10_User_Manuals/USER_MANUAL_01_AUTHENTICATION_LOGIN.md) | How to log in, password rules, what to do if locked out |
| 2 | [User Manual 02 — Dashboards](10_User_Manuals/USER_MANUAL_02_DASHBOARD_ALL_ROLES.md) | What you see after login, per role |
| 3 | [User Manual 06 — Trainee Management](10_User_Manuals/USER_MANUAL_06_TRAINEE_MANAGEMENT.md) | The day-to-day record |
| 4 | [User Manual 03 — Activities](10_User_Manuals/USER_MANUAL_03_ACTIVITIES_MANAGEMENT.md) | Programmes you run with trainees |
| 5 | [User Manual 04 — Attendance](10_User_Manuals/USER_MANUAL_04_ATTENDANCE_TRACKING.md) | Marking workflow (staff and trainee) |
| 6 | [User Manual 07 — Letters](10_User_Manuals/USER_MANUAL_07_LETTERS_DOCUMENTATION.md) | When generating official correspondence |
| 7 | [User Manual 05 — User & Staff](10_User_Manuals/USER_MANUAL_05_USER_STAFF_MANAGEMENT.md) | If you are an Admin — managing accounts |

The full index with reading orders for each role: [USER_MANUALS_MASTER_INDEX.md](10_User_Manuals/USER_MANUALS_MASTER_INDEX.md).

### Quick reference — UAT login credentials

For trying the system on the local instance during or after the demo:

| Role | Email | Password |
|---|---|---|
| Admin (cross-centre) | `super.admin@uat.creams.test` | `UatPass2026!` |
| Supervisor (Centre A) | `supervisor.a1@uat.creams.test` | `UatPass2026!` |
| Teacher (Centre A) | `teacher.a1@uat.creams.test` | `UatPass2026!` |
| AJK (Centre A) | `ajk.a1@uat.creams.test` | `UatPass2026!` |

Centres B and C have analogous accounts (`*.b1`, `*.c1`).

---

## 3. For IIUM committee members

You oversee the project. Your reading priority is governance and assurance:

| # | Document | Why |
|---|---|---|
| 1 | [Project governance](../CLAUDE.md) | The rules every developer (and AI agent) follows |
| 2 | [Multi-Centre Data Isolation](MULTI_CENTRE_ISOLATION.md) | The primary PDPA boundary, explained |
| 3 | [Staging Seed Policy](04_Deployment_Guides/STAGING_SEED_POLICY.md) | How real data is kept off staging/UAT/demo |
| 4 | [Manual 08 — System Administration](10_User_Manuals/USER_MANUAL_08_SYSTEM_ADMINISTRATION.md) | Operational reality + honest "what is NOT implemented" |
| 5 | [UAT Blockers register (Day 2)](audit/UAT_BLOCKERS_2026-04-30.md) | All findings from the reality audit, severity-classified |
| 6 | [Manual audit findings (Day 4)](audit/MANUAL_AUDIT_FINDINGS_2026-05-02.md) | What v1.0 docs got wrong and how v2.0 fixed it |
| 7 | [Git history audit (Day 3.5)](audit/git_history_audit_2026-05-01.log) | Historical PDPA exposure baseline (parked for post-delivery cleanup) |
| 8 | [Demo dry-run report (Day 5)](audit/DEMO_DRYRUN_REPORT_2026-05-03.md) | End-to-end verification that the demo path works |

### Security and PDPA assurance — quick statements

- **Authentication**: custom session-based via `POST /auth/check`. Bcrypt password hashing. Session regeneration on login. Rate limited (5 login attempts per minute per identifier+IP).
- **Authorisation**: role-based middleware on every protected route. Per-role permissions verified by 12 RBAC tests passing.
- **Data isolation**: 25 models scoped to `centre_id` (23 direct, 2 via asset relationship). Verified by dedicated isolation tests passing.
- **Audit trail**: every meaningful change captured in `*_audit_logs` tables with actor, action, old/new values, IP, timestamp. Append-only by convention.
- **Soft deletes** on critical tables — recoverable.
- **PDPA grep gate** in pre-commit hook blocks IC patterns and password literals.
- **Real data segregation**: `IRLSeeder` (real data) hard-gated to `APP_ENV=local`. Three-layer enforcement documented in `STAGING_SEED_POLICY.md`.
- **Test coverage**: 359 tests, 520 assertions, 0 failures (current baseline).
- **Known historical exposure**: 72 IC patterns in git history (pre-existing, parked for post-delivery cleanup).

---

## 4. For future developers / operators

When this project changes hands, start here:

| # | Document | Why |
|---|---|---|
| 1 | [CLAUDE.md (root)](../CLAUDE.md) | Governance, role definitions, commit rules, the do-not-repeat list |
| 2 | [docs/SOURCE_OF_TRUTH.md](SOURCE_OF_TRUTH.md) | The documentation index |
| 3 | [Local Setup Guide](LOCAL_SETUP_GUIDE_2026-05-04.md) | How to run CREAMS on a fresh machine |
| 4 | [Known Limitations](KNOWN_LIMITATIONS_2026-05-04.md) | Explicit list of what is NOT implemented |
| 5 | [Multi-Centre Isolation architecture](MULTI_CENTRE_ISOLATION.md) | The most important architectural concept |
| 6 | [Staging Seed Policy](04_Deployment_Guides/STAGING_SEED_POLICY.md) | How to deploy without leaking real data |
| 7 | [Production Rollback procedure](PRODUCTION_ROLLBACK.md) | What to do when a deploy goes wrong |
| 8 | [Commit Message SOP](COMMIT_MESSAGE_SOP.md) | Required commit format |
| 9 | [Codex Init Prompt](CODEX_INIT_PROMPT.md) | If using Codex CLI as the AI assistant |
| 10 | [Manual 08 — System Administration](10_User_Manuals/USER_MANUAL_08_SYSTEM_ADMINISTRATION.md) | Operational reality |

### Repository layout

```
CREAMS/
├── CLAUDE.md                  Governance for Claude Code
├── AGENTS.md                  Governance for Codex CLI
├── app/                       Laravel application code
│   ├── Http/Controllers/      Request handlers (custom auth in MainController)
│   ├── Models/                Eloquent models (with CentreScope where applicable)
│   ├── Models/Scopes/         CentreScope class (PDPA isolation)
│   ├── Http/Middleware/       Includes DemoInstanceMiddleware (handles /creams/{demo_id}/)
│   └── Providers/             RouteServiceProvider has rate limiter definitions
├── database/
│   ├── migrations/            All schema changes (chronological)
│   └── seeders/
│       ├── UATSeeder.php      Anonymised data — use for staging/UAT/demo
│       ├── IRLSeeder.php      Real data — LOCAL ONLY (hard-gated)
│       └── DatabaseSeeder.php The full chain (local only)
├── docs/                      All documentation (this file is here)
│   ├── 10_User_Manuals/       8 user manuals (re-baselined v2.0 May 2026)
│   ├── 04_Deployment_Guides/  STAGING_SEED_POLICY.md among others
│   ├── audit/                 Audit logs and reports
│   └── UAT FILES/             UAT scripts, demo script
├── routes/web.php             All HTTP routes (629 total)
├── tests/                     PHPUnit tests (359 passing)
└── .githooks/pre-commit       PDPA grep gate (install: git config core.hooksPath .githooks)
```

### Branch state

- **Current working branch**: `Fixers`
- **Main branch (default for PRs)**: `main`
- **Quarantined work**: `wip/abandoned-activity-category-2026-04-30` (orphaned model preserved for recovery, do not merge)
- **Sprint baseline tag**: `pre-sprint-baseline-2026-04-30` (the state before this sprint started)

---

## 5. What you receive in the handover

| Artefact | Where | Format |
|---|---|---|
| Source code | `git@<host>:<org>/CREAMS.git` (provided separately) | Git repository |
| Documentation tree | `docs/` in the repo | Markdown |
| User manuals (8) | `docs/10_User_Manuals/` | Markdown |
| UAT seed script | `database/seeders/UATSeeder.php` | PHP |
| Demo script | `docs/UAT FILES/DEMO_SCRIPT_2026-05-03.md` | Markdown |
| Test baseline | `docs/audit/test_baseline_2026-04-30.log` | Plain text |
| Audit register | `docs/audit/UAT_BLOCKERS_2026-04-30.md` | Markdown |
| Local setup guide | [LOCAL_SETUP_GUIDE_2026-05-04.md](LOCAL_SETUP_GUIDE_2026-05-04.md) | Markdown |
| Known limitations | [KNOWN_LIMITATIONS_2026-05-04.md](KNOWN_LIMITATIONS_2026-05-04.md) | Markdown |
| This handover doc | `docs/HANDOVER_PACKAGE_2026-05-04.md` | Markdown |

---

## 6. What is NOT included (and why)

- **No production hosting** — system runs on local dev only this sprint. Hosting is a phase-two decision.
- **No live UAT environment** — replaced by anonymised local UAT and live demo. A real staging instance is the next step.
- **No recorded demo video** — replaced by live in-person demo. If a video is needed later, the demo script can be filmed on a second pass.
- **No external security audit** — recommended pre-production step. Not commissioned in this sprint.
- **No load test data** — UATSeeder is small (designed for demo, not performance testing). A separate `PerfSeeder` would be needed for load tests.
- **No mobile app** — the web UI is responsive but no native mobile app exists.
- **No automated email distribution** — letters are PDFs, distribution is manual.

The full list of "claimed but not implemented" features is in [KNOWN_LIMITATIONS_2026-05-04.md](KNOWN_LIMITATIONS_2026-05-04.md).

---

## 7. What comes next (recommended sequence)

Phase 2 work, after this handover:

1. **Move CREAMS to a properly resourced VPS.** A $5–$10 Lightsail or equivalent. Co-tenancy with the Portfolio site is feasible; alternatively a dedicated instance.
2. **Stand up a staging environment** at a stable URL (suggested: `https://<host>/creams/uat/`) with `--class=UATSeeder` only, locked behind HTTP Basic Auth or IP allowlist.
3. **Real PPDK staff UAT cycle** — centre staff use the staging environment with seed data, sign off on workflows.
4. **External security audit** before any real-data deployment.
5. **Pilot rollout** — one centre, real users, real data, rollback plan ready.
6. **Post-rollout cleanup** — git history rewrite to remove the 72 historical IC patterns (disruptive but possible after the project is in operations hands).
7. **Phase 3 features** — Trainee and Parent self-service dashboards, MFA, automated email distribution, mobile-first redesign as desired.

Realistic timing for phase 2 (steps 1–3): **4–6 weeks** if there are no surprises in the UAT cycle.

---

## 8. Demo day reference (Day 7, 5 May 2026)

- **Demo script**: [docs/UAT FILES/DEMO_SCRIPT_2026-05-03.md](UAT FILES/DEMO_SCRIPT_2026-05-03.md)
- **Demo dry-run report**: [docs/audit/DEMO_DRYRUN_REPORT_2026-05-03.md](audit/DEMO_DRYRUN_REPORT_2026-05-03.md)
- **Pre-demo checklist**: in section 1 of the demo script
- **Backup plans**: in section 8 of the demo script
- **Anticipated questions and answers**: in section 6 of the demo script

After the demo, defects raised will be logged at `docs/audit/UAT_DEFECTS_2026-05-05.md` (file to be created on the day if needed). P0/P1 defects fixed during Day 7 buffer; P2/P3 added to phase 2 backlog.

---

## 9. Contacts

| Role | Person | Notes |
|---|---|---|
| Project lead / current maintainer | Faisal Hanafi | <faisalhanafi.dsa@gmail.com> |
| Operational handover (centre staff training) | (to be assigned by IIUM committee) | Recommended: identify a primary super-user per centre |
| Phase 2 hosting decision | (to be made by IIUM committee + Faisal) | Recommended timeline: within 2 weeks of this demo |

---

## 10. Sign-off

The intent of this handover is that:

1. PPDK centre staff can use the system after reading the user manuals (no developer support needed for daily operations).
2. The IIUM committee has full visibility into the security posture, governance, and what is/is not implemented.
3. A future developer or operator can rebuild the system from the source code and the docs in this package.
4. PDPA-protected data is never exposed in the demo, the manuals, the UAT environment, or the handover materials themselves.

If any of those four are not true after the demo, that's a deliverable failure. Raise it.

---

*Created: 4 May 2026 — sprint Day 6*
*Effective: end of Day 7 (5 May 2026)*
*Authoritative: this document points to the underlying authoritative files. When this and an underlying file disagree, the underlying file wins.*
