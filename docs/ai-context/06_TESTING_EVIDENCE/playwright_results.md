# CREAMS — Playwright Test Results

**Last updated**: 2026-06-24

---

## Full suite (218 tests) — 2026-06-24 RE-VERIFIED CURRENT

**Date run**: 2026-06-24 (second run — post AssetMovement alignment + PHANTOM-01 removal)
**Command**: `npx playwright test --reporter=line` from `tests/Browser/`
**Branch**: `Fixers` @ `07c5320`

| Metric | Value |
|---|---|
| Passed | **215** |
| Failed | **0** |
| Skipped | **3** (staff-crud 118, 119, 125 — pre-existing) |
| Duration | 21.4 minutes |

**Skipped tests** (pre-existing, not regressions):
- `staff-crud.spec.ts:169` — weak password validation
- `staff-crud.spec.ts:187` — duplicate email validation
- `staff-crud.spec.ts:295` — invalid update validation

**Verification purpose**: Confirmed no regressions after PHANTOM-01 classes scaffold removal (commit `9d23877`) and AssetMovement model rewrite (commit `07c5320`). Result matches prior 215/0/3 baseline exactly.

---

## Full suite (218 tests) — 2026-06-24 PRIOR RUN

**Date run**: 2026-06-24 (first run this day — post IEP spec fix)
**Command**: `npx playwright test` from `tests/Browser/`
**Branch**: `Fixers` @ `8e1e2ff`

| Metric | Value |
|---|---|
| Passed | **215** |
| Failed | **0** |
| Skipped | **3** (staff-crud 118, 119, 125 — pre-existing) |
| Duration | ~20.5 minutes |

**Test fixed this session**:
- `functional/iep-management.spec.ts:102` "IEP details shows goals and objectives"
  — was failing 214/1/3 before fix. Root cause: CSS comma union locator bug +
  click-navigation race. Fix: `getAttribute('href')` + `page.goto()` + `waitForLoadState`.

---

---

## Demo-flow spec (99-demo-flow.spec.ts) — CURRENT

**Date run**: 2026-05-04
**Result**: **8/8 PASSED** in 24.3 seconds
**Evidence**: `docs/audit/PLAYWRIGHT_FINDINGS_2026-05-04.md`

| Beat | Test | Result |
|---|---|---|
| A1 | Admin full login UI flow | ✅ PASS |
| A2 | Admin dashboard renders with data | ✅ PASS |
| A3 | Trainee list shows UAT trainees (`/trainees/home`) | ✅ PASS |
| A4 | Activities list shows UAT activities | ✅ PASS |
| A5 | Centres list shows UAT centres | ✅ PASS |
| B1 | Supervisor dashboard — centre-scoped | ✅ PASS |
| — | Teacher dashboard | ✅ PASS |
| — | AJK dashboard | ✅ PASS |

**Screenshots**: 12 files in `docs/audit/screenshots/demo/`

---

## MCP interactive dry-run — CURRENT

**Date run**: 2026-05-04
**Tool**: Playwright MCP (`mcp__playwright__*` tools)
**Result**: **6/6 beats verified**
**Evidence**: `docs/audit/PLAYWRIGHT_MCP_DRYRUN_2026-05-04.md`

Key observations:
- CSP blocks external CDNs (Bootstrap, jQuery, FontAwesome) — P2
- Weather widget (`wttr.in`) blocked by `connect-src` CSP — P3
- `GET /auth/logout` does not clear session — use logout button (POST) — P2

---

## Full existing Playwright suite — STALE

**Date run**: 2026-05-04
**Result**: 181 passed / **26 failed** / 3 skipped

**Root cause of 26 failures**: Stale spec files from 5+ months ago using old credentials (`lakshmi.krishnan@iium.edu.my` etc.) and old route patterns.

**Classification**: P3 — none of the failures indicate app defects. The demo-flow spec (`99-demo-flow.spec.ts`) supersedes these for demo-readiness purposes.

**Phase-2 action**: Rewrite the 16 stale spec files to use current UAT credentials and routes.

---

## Playwright configuration notes

| Setting | Value | File |
|---|---|---|
| Base URL | `http://localhost:8000` | `tests/Browser/playwright.config.ts` |
| Browser | Chromium (headless) | same |
| Workers | 1 (serial) | same |
| Timeout | 90s navigation, 5s expect | same |
| Video | Always on | same |
| Screenshots | Always on | same |
| Auth state dir | `tests/Browser/.auth/` | `global-setup.ts` |
| Demo screenshot dir | `docs/audit/screenshots/demo/` | `99-demo-flow.spec.ts` |

---

## Known Playwright issues

| Issue | Status |
|---|---|
| `test-results/` wiped each run — do not save custom screenshots there | DOCUMENTED (FAIL-02) |
| Rate limiter blocks global-setup if run more than once per minute | DOCUMENTED (FAIL-04) |
| Browser binaries at `~/AppData/Local/ms-playwright/` — not in repo | INSTALLED (2026-05-04) |
| `global-setup.ts` uses new UAT credentials | FIXED (2026-05-04) |

---

## Playwright MCP config

File: `.mcp.json` at repo root.

```json
{
  "mcpServers": {
    "playwright": {
      "command": "cmd",
      "args": ["/c", "npx", "-y", "@playwright/mcp@latest", "--browser", "chrome", "--viewport-size", "1280,800"]
    }
  }
}
```

Requires Claude Code restart to load. Uses system Chrome.
