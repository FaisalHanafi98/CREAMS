# Baseline Test Reports — 2026-06-12

> **Branch**: `Fixers` (synced to main `82aabd1` + governance commits)
> **Stack**: Laravel 12.58 / PHP 8.5.4 local / PHPUnit 11.5 / Playwright (Chromium)
> **Status labels**: VERIFIED (fresh runs, this session)

---

## Report 1 — PHPUnit suite (unit + feature) — VERIFIED PASS

```
php -d memory_limit=1G artisan test
Tests:    374 passed (603 assertions)
Duration: ~40s
```

- 0 failures, 0 errors. Exceeds the previous 359/520 floor (growth = PublicBlockerFixTest + expanded Asset/Trainee suites from main).
- `TEST_BASELINE.md` updated with this floor on 2026-06-11.
- Note: always run with `memory_limit=1G` — collision's failure renderer OOMs at the default 128M.

## Report 2 — Playwright browser suite — 195/218 PASS, 23 FAIL (baseline, pre-fix-loop)

Suite is **218 tests** (stale docs said 181/210 — superseded). Run took ~45 min, single worker.
One fix already landed before the run: `00-diagnostic.spec.ts` stale credential
(`lakshmi.krishnan@iium.edu.my` → `super.admin@uat.creams.test`); diagnostic now 3/3.

### Failure classification (QA_OPERATING_MANUAL order)

| Class | Count | Tests | Evidence | Assessment |
|-------|-------|-------|----------|------------|
| **Environment / infra** | 13 | dashboard-access (admin/ajk/supervisor/teacher), asset/centre/IEP/messages page-loads, unauthorized #23, others | `Error: worker process exited unexpectedly (code=3221225794)` = 0xC0000142, Windows DLL-init failure in the Chromium worker | **Not app defects.** Machine was under heavy load (multiple AI sessions + 6 leftover Chromium instances). Re-run on a quiet machine before treating as real |
| **Login redirect timeouts** | 10 | auth/login ×4 roles, auth/logout ×4 roles + 2 re-login | `TimeoutError: page.waitForURL: Timeout 85000/90000ms exceeded` after submitting login | Prime suspect: `throttle:login` rate limiter (5/min keyed on `email` input or IP). `global-setup.ts` itself documents this trap — it sends an extra `email` field to get per-user buckets. The login/logout specs perform many interactive logins in quick succession and may be hitting the IP bucket. Secondary suspect: first-request view-compile latency under load |

### Verdict

**No proven application defect in the 23 failures yet.** Both failure classes point at test
environment and rate-limiting interactions, not product code. The diagnostic login, all
RBAC restricted-access checks, all functional CRUD flows except the crashed page-loads,
and the entire unauthorized suite (except the one worker crash) passed.

### Next loop iteration (when resumed)

1. Re-run only the 23 failed tests on a quiet machine: `npx playwright test --last-failed` (or by file) with no other AI sessions/Chromium instances running.
2. If login timeouts persist: capture the post-submit page state — if it shows the
   "Too many login attempts" error, fix = align login/logout specs with the
   global-setup pattern (send `email` field) or relax `RATE_LIMIT_LOGIN` in `.env.testing`-equivalent for local Playwright runs. App code change NOT indicated.
3. If 0xC0000142 persists in isolation, reinstall Playwright browsers (`npx playwright install chromium`).
4. Regression gate after any change: full PHPUnit + full Playwright.

### Operational notes

- ~45 min for the full browser suite; plan runs accordingly or use `--grep @smoke` subsets.
- Two session restarts during the run orphaned the runner's parent shell; the runner
  completed all 218 tests but the summary line was lost — counts above derived from the
  per-test log (`/tmp/pw_baseline.txt`, 725 lines, 23 numbered failure entries).
