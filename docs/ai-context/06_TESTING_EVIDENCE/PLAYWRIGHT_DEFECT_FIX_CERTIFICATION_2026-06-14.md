# Playwright Browser Test — Defect Fix Certification

**Date:** 2026-06-14
**Branch:** Fixers
**Scope:** Test harness only — no application source code modified
**Final result:** 215 passed / 3 skipped / 0 failed (218 total)

---

## Defects Resolved

### PW-001 — Activity Wizard CRUD Failures

**Root Cause 1 — Instructor qualification seeding**

All 15 UAT staff have `education_specialization = NULL` and
`teaching_specialization = NULL`. `InstructorQualificationRule` requires at
least one keyword match (e.g., "Special Education", "Rehabilitation") for
activities of type `rehabilitation` (which includes "Autism Spectrum Support").
With NULL columns every instructor is rejected and the POST to `activities.store`
fails validation before any activity is created.

Fix: `beforeAll` in `activity-crud.spec.ts` seeds all teacher/supervisor/admin
staff with `education_specialization = 'Special Education'` and
`teaching_specialization = 'Rehabilitation'` via `spawnSync` (not `execSync`,
to bypass cmd.exe shell-escaping on Windows). `afterAll` restores NULL.
Technique switched from `App\Models\User` (wrong — maps to same table but had
escaping issues) to `DB::table('staffs')` with direct args array.

**Root Cause 2 — `learning_outcomes` nested-array injection**

`submitForm()` previously injected `learning_outcomes[0][outcome_title]`
(nested PHP array format). The controller validates `learning_outcomes` as
`nullable|string|max:2000`. PHP receives an array → the string rule fails →
server redirects back to `/create`.

Fix: Changed injection to a single plain string:
`injectOrSet('learning_outcomes', 'Basic Skill Development: ...')`.

**Root Cause 3 — `difficulty_level` capitalisation**

`submitForm()` capitalised the radio button value to `'Beginner'` based on a
wrong assumption that the server expected title-case. The controller validates
`nullable|in:beginner,intermediate,advanced` (lowercase). Sending `'Beginner'`
fails the `in:` check → server redirects back to `/create`.

Fix: Removed the capitalisation. The radio button already emits the correct
lowercase value (`'beginner'`); `injectOrSet` now passes it through unchanged,
with `'beginner'` as the fallback.

**Files changed:**
- `tests/Browser/tests/functional/activity-crud.spec.ts` — beforeAll/afterAll
- `tests/Browser/pages/ActivityPage.ts` — submitForm() learning_outcomes and difficulty_level

**Result:** 19/19 tests pass.

---

### PW-002 — Trainee CRUD Failures

Resolved in the session immediately prior (2026-06-13).

**Root Causes:** Test data used wrong selector strategies for the new
Bootstrap 5 layout; IC/email uniqueness not ensured per test; delete
flow expected a different confirmation dialog pattern.

**Files changed:** `tests/Browser/pages/TraineePage.ts`,
`tests/Browser/tests/functional/trainee-crud.spec.ts`,
`tests/Browser/fixtures/test-data.ts`

**Result:** 17/17 tests pass.

---

### PW-003 — Asset Management Route Wrong URL

Resolved in the session immediately prior (2026-06-13).

**Root Cause:** Test navigated to `/assets` but the actual route is
`/centre/assets`.

**Files changed:** `tests/Browser/tests/functional/asset-management.spec.ts`

**Result:** 9/9 tests pass.

---

## Full Suite Run — 2026-06-14

Command run from `tests/Browser/`:

```
npx playwright test --reporter=list
```

| Spec file | Passed | Skipped | Failed |
|-----------|--------|---------|--------|
| 00-diagnostic.spec.ts | 3 | 0 | 0 |
| 99-demo-flow.spec.ts | 8 | 0 | 0 |
| functional/activity-crud.spec.ts | 19 | 0 | 0 |
| functional/asset-management.spec.ts | 9 | 0 | 0 |
| functional/centre-crud.spec.ts | (included in total) | — | 0 |
| functional/iep-management.spec.ts | 12 | 0 | 0 |
| functional/messages-notifications.spec.ts | 11 | 0 | 0 |
| functional/staff-crud.spec.ts | 17 | 3 | 0 |
| functional/trainee-crud.spec.ts | 17 | 0 | 0 |
| rbac/admin-access.spec.ts | 14 | 0 | 0 |
| rbac/ajk-access.spec.ts | 14 | 0 | 0 |
| rbac/supervisor-access.spec.ts | 16 | 0 | 0 |
| rbac/teacher-access.spec.ts | 14 | 0 | 0 |
| rbac/unauthorized.spec.ts | 15 | 0 | 0 |
| **TOTAL** | **215** | **3** | **0** |

The 3 skipped tests (`staff-crud.spec.ts:169`, `:187`, `:295`) are marked
`test.skip()` in the spec file and were pre-existing before this work began.

---

## Constraint Compliance

- No application source code modified (test harness only)
- No real PII used in test data (synthetic names, random suffixes)
- Instructor qualification seeding uses `afterAll` teardown to restore NULL state
- Commit will carry no AI attribution per governance rule (2026-06-12)
