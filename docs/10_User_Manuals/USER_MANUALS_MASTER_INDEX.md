# CREAMS User Manuals — Master Index

**Version**: 2.0 (re-baselined 2 May 2026 — sprint Day 4)
**Status**: All 8 manuals re-baselined against running app and current code
**Supersedes**: Version 1.0 (deprecated — drift across most manuals after the 5-month gap)

---

## Summary

This index lists all 8 user manuals. Each manual was rewritten to v2.0 in May 2026 after a code-and-app audit found significant drift between the v1.0 manuals and the actual implementation. The v2.0 manuals are concise (typically 100–200 lines), accurate, and cross-referenced to source code locations.

For the per-manual audit findings (what was wrong in v1.0 and how v2.0 fixes it), see `docs/audit/MANUAL_AUDIT_FINDINGS_2026-05-02.md`.

---

## Manual catalogue

| # | File | Audience | Purpose | Status |
|---|---|---|---|---|
| 01 | `USER_MANUAL_01_AUTHENTICATION_LOGIN.md` | All users | Login, identifier formats, password policy, forgot-password, sessions | v2.0 (current) |
| 02 | `USER_MANUAL_02_DASHBOARD_ALL_ROLES.md` | All users | Dashboard layout, stats tiles, per-role differences | v2.0 (current) |
| 03 | `USER_MANUAL_03_ACTIVITIES_MANAGEMENT.md` | Admin, Supervisor, Teacher | Create/edit activities, six disability-support categories, sessions, enrolment | v2.0 (current) |
| 04 | `USER_MANUAL_04_ATTENDANCE_TRACKING.md` | All staff | Staff attendance vs trainee session attendance — separate systems | v2.0 (current) |
| 05 | `USER_MANUAL_05_USER_STAFF_MANAGEMENT.md` | Admin, Supervisor | Create/edit accounts, role and centre assignment, soft-delete | v2.0 (current) |
| 06 | `USER_MANUAL_06_TRAINEE_MANAGEMENT.md` | Admin, Supervisor, Teacher | Trainee record schema, registration, audit trail, PDPA reminders | v2.0 (current) |
| 07 | `USER_MANUAL_07_LETTERS_DOCUMENTATION.md` | Admin, Supervisor | Templates with `{{variables}}`, PDF generation via DomPDF, history | v2.0 (current) |
| 08 | `USER_MANUAL_08_SYSTEM_ADMINISTRATION.md` | Operator / dev | CLI operations, sources of truth, security posture, what-is-NOT-implemented | v2.0 (current) |

---

## Suggested reading order

### For PPDK rehab centre staff (Teacher / Supervisor / AJK)

1. Manual 01 (Authentication) — get logged in
2. Manual 02 (Dashboard) — understand what you see after login
3. Manual 06 (Trainees) — the day-to-day record
4. Manual 03 (Activities) — what programmes are running
5. Manual 04 (Attendance) — daily marking workflow
6. Manual 07 (Letters) — when generating official correspondence

### For Admin staff

1. Manuals 01, 02, 03, 04, 06, 07 above
2. Manual 05 (User & Staff Management) — managing accounts
3. Manual 08 (System Administration) — operational reality, what is NOT implemented

### For IIUM committee stakeholders

1. Manual 01 (Authentication) — the security model
2. Manual 02 (Dashboard) — what each role sees
3. Manual 06 (Trainees) — the PDPA-protected core data and the audit trail
4. Manual 08 (System Administration) — the security posture, governance, sources of truth

### For developers / new maintainers

Skip the manuals. Start with `CLAUDE.md` (root), then `docs/SOURCE_OF_TRUTH.md`, then `docs/MULTI_CENTRE_ISOLATION.md`. Use the manuals to understand user-facing workflows.

---

## Cross-references

| If you want to know about... | Read |
|---|---|
| Centre data isolation architecture | `docs/MULTI_CENTRE_ISOLATION.md` |
| Staging deployment policy | `docs/04_Deployment_Guides/STAGING_SEED_POLICY.md` |
| Commit message format | `docs/COMMIT_MESSAGE_SOP.md` |
| Project governance for AI agents | `CLAUDE.md`, `AGENTS.md`, `docs/CODEX_INIT_PROMPT.md` |
| Test baseline | `docs/audit/test_baseline_2026-04-30.log` |
| Routes inventory | `docs/audit/routes_2026-04-30.json` |
| What v1.0 manuals got wrong | `docs/audit/MANUAL_AUDIT_FINDINGS_2026-05-02.md` |
| Production rollback procedure | `docs/PRODUCTION_ROLLBACK.md` |

---

## Documentation hygiene rules

When updating any manual:

1. **Verify against code first.** Open the relevant controller, model, migration, route, or rendered HTML. Do not rely on memory or older documentation.
2. **Mark version and date.** Bump the version string in the manual header. Note what was superseded.
3. **Be specific.** Say "minimum 12 characters" not "strong password". Say "rate limited 5/min" not "secure".
4. **List what is NOT implemented.** Explicitly call out features that earlier docs mentioned but the system does not have. This protects future readers from the same drift.
5. **Source-of-truth footnote.** End each manual with a "Source of truth" line listing the actual code files / schemas / migrations that the manual is verified against.

---

*Updated: 2 May 2026 — sprint Day 4*
*Maintainer: Faisal Hanafi*
