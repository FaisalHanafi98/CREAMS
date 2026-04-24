# CREAMS Source of Truth

> **Document type**: Documentation router (authority index)
> **Scope**: All CREAMS sessions, all agents
> **Supersedes**: `docs/CLAUDE.md` (stale — Feb 2026), `docs/CONSOLIDATED_DOCUMENTATION_INDEX.md` (stale — Jan 2025)
> **Last containment pass**: 2026-04-24

---

## Read this first

This file is the **single documentation entry point** for CREAMS. Every new session, every subagent, every contributor starts here.

### Hard facts
1. **The root SOP at `../CLAUDE.md` (repository parent) governs this project.** Anything in CREAMS docs that conflicts with it loses.
2. **Archived docs under `docs/archive/` are historical only.** Do not cite them as current. Do not follow their instructions.
3. **Old session prompts do not guide current work.** The only active session prompt is `docs/CREAMS_SESSION_CURRENT.md`.
4. **Current deployment target is Lightsail** (shared $5 instance with Portfolio) per `docs/LIGHTSAIL_FOOTPRINT.md` — **pending code-reality verification**. Any guide that says otherwise (Vercel, ECS/Fargate, generic AWS) has been archived under `docs/archive/deployment/`.
5. **Do not assume current state from documentation.** CREAMS's documentation has drifted. Verify through code, tests, and running the application before claiming anything is done.

---

## Authority table

Every file below is **tentatively active** but requires freshness verification before being trusted for a specific decision. The "Verify against" column shows what to cross-check before citing.

| Topic | File | Verify against |
|-------|------|----------------|
| Current session mission, exit criteria, DO NOTs | [`CREAMS_SESSION_CURRENT.md`](CREAMS_SESSION_CURRENT.md) | `git log` on `app/`, `tests/` since the session's stated date |
| Multi-centre data isolation (CentreScope coverage + documented exceptions) | [`MULTI_CENTRE_ISOLATION.md`](MULTI_CENTRE_ISOLATION.md) | `grep -r "CentreScope\|HasCentreScope" app/Models/` |
| Test baseline (current count, pass rate) | [`TEST_BASELINE.md`](TEST_BASELINE.md) | `php artisan test` then compare |
| Deployment target + resource footprint | [`LIGHTSAIL_FOOTPRINT.md`](LIGHTSAIL_FOOTPRINT.md) | Actual Lightsail console + `.env.production` |
| Pre-deploy security gate | [`PRE_DEPLOY_SECURITY_CHECKLIST.md`](PRE_DEPLOY_SECURITY_CHECKLIST.md) | Item-by-item verification in code |
| Module/feature inventory | [`MODULE_FUNCTIONALITY_INVENTORY.md`](MODULE_FUNCTIONALITY_INVENTORY.md) | `routes/web.php` + actual controllers |
| Current security audit findings | [`SECURITY_AUDIT_REPORT.md`](SECURITY_AUDIT_REPORT.md) | Code state after April 2026 hardening |

All of the above **require freshness verification** before being used to justify a new code change.

---

## Architecture decisions (still authoritative — architecture has not changed)

- [`ADR-001-blade-over-spa.md`](ADR-001-blade-over-spa.md)
- [`ADR-002-six-role-rbac.md`](ADR-002-six-role-rbac.md) — roles: Admin, Supervisor, Teacher, AJK (+ Trainee, Parent as future). **Not** Admin/Manager/Staff/Caretaker.
- [`ADR-003-mysql-over-postgresql.md`](ADR-003-mysql-over-postgresql.md)

---

## What you must NOT trust

Loading any of the following will mislead a new session. They remain on disk for history only.

| File / path | Why |
|-------------|-----|
| `docs/archive/prompts/*` | Superseded session prompts, pre-SOP Claude prompts, completed re-eval prompts |
| `docs/archive/deployment/*` (AWS, Vercel guides) | Deployment targets that were never actually used or have been abandoned |
| `docs/archive/duplicates/user_manuals_copy/` | Byte-identical duplicate of `10_User_Manuals/` |
| `docs/archive/quarantine/*` | Malformed filenames and filesystem artefacts — do not open inline |
| `docs/CLAUDE.md` (still on disk, unmoved) | Feb 2026 — claims wrong auth stack (Breeze+Sanctum), wrong role names, wrong coverage targets. Awaits later cleanup session. |
| `docs/CONSOLIDATED_DOCUMENTATION_INDEX.md` (still on disk) | Dated January 2025. Routes readers to non-existent folders and stale "first prompt" templates. Awaits later cleanup session. |
| `docs/CREAMS_CODEBASE_DOCUMENTATION.md` (Dec 2025) | Pre-dates CentreScope, security waves, CI/CD, ADRs. 4+ months stale. |
| `docs/06_Status_Reports/*` files with "FINAL" / "COMPLETE" / "ULTIMATE" in the name | Point-in-time demo-era snapshots — title language is misleading. |

---

## Known drift watchlist (contradictions still present on disk)

1. **Test count** — at least five different numbers across active and historical docs. Only `TEST_BASELINE.md` is current; ignore test counts stated in roadmap/inventory headers.
2. **CentreScope coverage** — older session docs say 3 or 4 scoped models. Current truth is 26-of-28 + two documented exceptions, per `MULTI_CENTRE_ISOLATION.md`.
3. **Deployment target** — older docs reference Vercel or ECS. Current target is Lightsail (see above).
4. **Role names** — `docs/CLAUDE.md` claims Admin/Manager/Staff/Caretaker/Trainee/Parent. Real roles are Admin/Supervisor/Teacher/AJK (+ Trainee, Parent planned) — see ADR-002.
5. **Auth stack** — `docs/CLAUDE.md` claims Breeze+Sanctum. Real stack is custom session-based auth via `POST /auth/check`.

---

## How to update this file

1. Verify every link in the authority table still resolves and still reflects current code.
2. If an authority file becomes stale, either refresh it in place or archive it and remove its row here.
3. Bump the **Last containment pass** date at the top.
4. Commit with scope `docs` and a clear "why".

Do **not** add a new authority file without removing or merging an existing one. The authority table is capped at ~8 rows by design.
