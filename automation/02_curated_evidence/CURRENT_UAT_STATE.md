# CREAMS — Current UAT State

> **Source**: `UAT_INVENTORY.md`, `SMOKE_TEST_INVENTORY.md`, `docs/audit/live_*_uat_*.md`, `TEST_BASELINE.md`, `PLAYWRIGHT_FINDINGS_2026-05-04.md`
> **Date**: 31 May 2026
> **Rule**: Only confirmed UAT evidence. Oct 2025 historical data excluded from "current" status.

---

## PHPUnit Test Baseline

| Metric | Value | Source |
|--------|-------|--------|
| Tests | 359 | `test_baseline_2026-04-30.log` |
| Assertions | 520 | Same |
| Failures | 0 | Same |
| Errors | 0 | Same |
| Test files | 37 | `TEST_BASELINE.md` |
| Coverage estimate | ~15-20% | `TEST_BASELINE.md` |
| Current (May 8) | 354/359 (5 failures from demo_demo_route typo) | `test_commands.md` |
| Floor rule | 359 — no session may drop below | `TEST_BASELINE.md` |

---

## Playwright E2E

| Metric | Value | Source |
|--------|-------|--------|
| Full suite | 181/210 passing (86.2%) | `PLAYWRIGHT_FINDINGS_2026-05-04.md` |
| Demo-flow spec | 8/8 passing | `playwright_results.md` |
| MCP dry-run | 6/6 beats passed | `PLAYWRIGHT_MCP_DRYRUN_2026-05-04.md` |
| Failing tests | 29 — categorized as: post-submit redirect timeout (14), activity wizard incomplete (12), performance threshold (1), browser context closed (2) | `TEST_STABILIZATION_REPORT.md` (Feb 2026) |
| Screenshots | 12 (demo spec), 108 (full browser retest) | Various audit reports |

---

## Live Production UAT — pdk-creams.org (May 2026)

### FAIL — Confirmed Failing Flows

| Flow | Status | First Detected | All Runs | Evidence |
|------|--------|---------------|----------|----------|
| **Logout session termination** | FAIL | May 16 | May 16-18 (all runs) | `live_uat_gate_smoke_2026-05-17.md` |
| **Trainee creation** | FAIL | May 16 | May 16-18 (all runs) | `live_functional_uat_readiness_2026-05-16.md` |
| Authenticated UAT blocked (all accounts redirect to login) | FAIL | May 16 rerun | May 16 rerun, May 18 Edge | `live_invasive_uat_rerun_2026-05-16_0905.md` |
| Edge Chromium — all credentials fail ("No account found") | FAIL | May 18 | May 18 (one run) | `full_browser_uat_report_20260518T153020Z.md` |
| Contact form rejects valid emails | FAIL | May 18 | May 18 (Edge run only) | `full_browser_uat_report_20260518T153020Z.md` |

### PASS — Confirmed Working

| Flow | Status | Evidence |
|------|--------|----------|
| All 4 role logins | PASS | `live_uat_gate_smoke_2026-05-17.md` (latest gate) |
| Asset detail page (200) | PASS | `live_uat_gate_smoke_2026-05-17.md` |
| No 500 errors on main pages | PASS | `live_uat_gate_smoke_2026-05-17.md` |
| No template links | PASS | `live_uat_gate_smoke_2026-05-17.md` |
| RBAC enforcement | PASS | `live_smoke_claim_verification_2026-05-16.md` |
| Asset 500 previously fixed | PASS (confirmed) | `live_smoke_claim_verification_2026-05-16.md` |
| Synthetic record creation | PASS | `live_mutation_smoke_2026-05-15.md` |
| CRUD operations | PASS | `live_mutation_smoke_2026-05-15.md` |

---

## Module UAT Status (from May 2026 Live Runs)

| Module | Status | Notes |
|--------|--------|-------|
| Authentication | PASS | All 4 roles login OK |
| Dashboard | PASS | Per-role dashboards load |
| Assets | PASS | Detail page 200, creation works |
| Centre Management | PASS | Admin can manage centres |
| Activities | UNKNOWN | Not exhaustively tested in live runs |
| Attendance | UNKNOWN | Not exhaustively tested in live runs |
| Trainees | FAIL | Creation 500 error |
| Letters | UNKNOWN | Not exhaustively tested in live runs |
| Messages | UNKNOWN | Not exhaustively tested in live runs |
| Volunteers | UNKNOWN | Not exhaustively tested in live runs |
| Session Management | FAIL | Logout does not terminate sessions |

---

## Demo Dry-Run Status

- HTTP-level walkthrough: all demo pages return 200. RBAC enforced. Centre isolation verified.
- Source: `DEMO_DRYRUN_REPORT_2026-05-03.md`
- Demo script: ready — `DEMO_SCRIPT_2026-05-03.md` (45 mins, 13 beats)
- Stakeholder demo: NOT SAFE — "Site not safe for stakeholder demo" per May 16 invasive UAT
- Source: `live_invasive_uat_admin_credential_rerun_2026-05-16.md`

---

## Production Readiness Verdict

**NOT READY FOR PRODUCTION** — 2 persistent blockers (logout, trainee create). Pre-deploy gate uncleared (1 RED, 2 YELLOW). Stakeholder demo blocked. All May 2026 live UAT runs: FAIL.
