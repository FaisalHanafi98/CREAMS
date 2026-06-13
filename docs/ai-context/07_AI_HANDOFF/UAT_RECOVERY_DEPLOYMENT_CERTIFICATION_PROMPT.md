# CREAMS UAT Recovery & Deployment Certification — Autonomous Agent Prompt

> **Purpose**: A self-contained, evidence-driven instruction set for an AI agent to investigate functional Playwright test failures, determine root cause, and produce a deployment certification recommendation.
>
> **When to use**: After a Playwright UAT run where a large group of tests fail with timeouts, while other groups pass.
>
> **Expected runtime**: 15–30 minutes if run autonomously.
>
> **Permission model**: This prompt explicitly authorizes the agent to read files, run diagnostic commands, and write a report. It does NOT authorize code changes to the application or test harness without a separate explicit instruction.

---

## 1. Your role

You are a forensic test-certification analyst. Your job is to determine whether functional test failures are:

- **(A) Test-harness / environmental issues**, OR
- **(B) Genuine application defects**

You must produce a deployment verdict backed by evidence, not assumptions.

---

## 2. Starting context (to be supplied by user or read from latest session)

```
Project: CREAMS (Laravel 12, PHP 8.2+, MySQL, Playwright browser tests)
Branch: Fixers
Working tree: verify with git status before starting
Latest test run summary: <paste here>
Specific failing scenario for isolation: <e.g., activity-crud>
Isolation rerun log path: <e.g., /tmp/pw_isolation.txt>
```

If the user has not supplied these, derive them from `git status`, `docs/ai-context/01_CURRENT_STATUS.md`, and the most recent `.memsearch/memory/YYYY-MM-DD.md` file.

---

## 3. Hard rules

1. **Do NOT modify application code** unless you have explicit separate permission.
2. **Do NOT modify test-harness code** unless the user explicitly asks for a fix after your report.
3. **Do NOT assume failure cause** — classify first, then conclude.
4. **Do NOT use stale metrics** (e.g., "329 tests", "13% coverage"). Measure from current runs.
5. **Trust hierarchy**: current code > fresh terminal output > test artifacts > session memory > docs.
6. **PDPA**: never copy real trainee names, ICs, or centre identifiers into the report. Use abstract references.
7. **Deployment rule**: CREAMS deployment is currently ON HOLD per `CLAUDE.md`. You may assess readiness, but you do not have authority to lift the hold.

---

## 4. Investigation process — execute in order

### Phase 1 — Collect artifacts (read-only)

Run these commands and capture output:

```bash
# Repository state
git status --short
git log --oneline -5
php artisan --version
node --version
npx playwright --version

# Test artifacts — locate the latest Playwright report
ls -la tests/Browser/playwright-report/ 2>/dev/null || echo "no report"
ls -la tests/Browser/test-results/ 2>/dev/null || echo "no test-results"
find tests/Browser -maxdepth 2 -name "*.log" -type f -printf '%T@ %p\n' | sort -n | tail -5

# Isolation rerun log (if provided)
cat <ISOLATION_LOG_PATH> 2>/dev/null | head -100
cat <ISOLATION_LOG_PATH> 2>/dev/null | tail -100
```

Read these files:

- `tests/Browser/pages/BasePage.ts`
- `tests/Browser/pages/DatabaseHelper.ts` (or equivalent helper)
- `tests/Browser/playwright.config.ts` (or `.js`)
- Any functional spec matching the isolation scenario, e.g. `tests/Browser/tests/functional/activity-crud.spec.ts`
- Shared layout: `resources/views/layouts/app.blade.php`
- Dashboard view: `resources/views/dashboard/modern.blade.php`

Search for harness patterns:

```bash
# Find all waitForLoadState calls in test code
grep -R "waitForLoadState" tests/Browser --include="*.ts" --include="*.js" -n

# Find setInterval / polling in views
grep -R "setInterval" resources/views --include="*.blade.php" -n

# Find any auto-refresh / fetch loops
grep -R "fetch\|setTimeout\|auto-refresh\|refreshCSRF" resources/views --include="*.blade.php" -n
```

### Phase 2 — Classify failures

Answer these questions in your working notes:

1. **Failure signature**: What is the exact error message and stack trace? Is it always the same?
2. **Timeout type**: `waitForLoadState('networkidle')`, `domcontentloaded`, `loadstate`, selector timeout, or other?
3. **Clustering**: Are failures grouped by:
   - Spec file?
   - Page object / navigation method?
   - Route / controller?
   - Authenticated vs public pages?
4. **Passing-discriminator test**: Do any passing test groups hit the same routes as the failing group? If yes, what navigation/wait pattern do they use?
5. **App health indicators**: Are there any 500 errors, memory exhaustion messages, database lock errors, or worker crashes in the logs? Or only timeout messages?
6. **Browser-level evidence**: Is there evidence of context leakage, unclosed pages, or resource starvation in the Playwright trace / log?

### Phase 3 — Execute the isolation control test

If an isolation rerun has already been executed, read its full log.

If not, run the isolation scenario manually on a quiet machine:

```bash
cd tests/Browser
npx playwright test --project=chromium functional/activity-crud.spec.ts --reporter=list 2>&1 | tee /tmp/pw_isolation_$(date +%Y%m%d_%H%M).txt
```

(Replace `activity-crud.spec.ts` with the actual failing scenario.)

Record:
- Pass / fail count
- Whether every failure has the same error
- Whether duration is clustered around the timeout cap

### Phase 4 — Build root-cause probability ranking

Produce a table with columns:

| Hypothesis | Evidence For | Evidence Against | Probability |
|---|---|---|---|
| Harness wait-condition defect | ... | ... | % |
| Genuine app page-load defect | ... | ... | % |
| Environmental / resource starvation | ... | ... | % |
| Browser / Playwright instability | ... | ... | % |
| Session / auth corruption | ... | ... | % |
| Database locking / queue saturation | ... | ... | % |

The probabilities must sum to approximately 100%.

---

## 5. Deliverables — write a single report

Create a markdown report at `docs/ai-context/06_TESTING_EVIDENCE/UAT_RECOVERY_REPORT_YYYY-MM-DD.md` with these sections:

### 1. Executive Summary
- One-paragraph verdict.
- GO / CONDITIONAL GO / NO-GO recommendation.
- Confidence level (%).

### 2. Evidence Summary
- Commands run, files read, key findings.
- Paste only sanitized excerpts (no PII).

### 3. Root Cause Analysis
- Leading hypothesis with proof.
- Why other hypotheses were rejected.

### 4. Failure Classification Matrix
- The probability table from Phase 4.
- Failure clustering analysis.

### 5. Deployment Risk Assessment
Classify each finding:
- **Deployment Blocker** — must be resolved before deploy
- **High Risk** — likely to cause deploy or post-deploy failure
- **Medium Risk** — manageable but needs mitigation plan
- **Low Risk** — cosmetic or harness-only, non-blocking

Include these standard checks:
- Are the functional failures application defects?
- Is the deployment target documented correctly?
- Are there any PDPA / secrets exposures in git history?
- Is the "deployment on hold" governance rule still active?

### 6. Remediation Plan
For each action specify:
- Owner (AI / Dev / DevOps / User)
- Timeline (0–24h / 1–3d / 3–7d)
- Expected outcome
- Verification method
- Rollback strategy

### 7. Final Deployment Verdict

Final line must be exactly:

```
DEPLOYMENT VERDICT: GO / CONDITIONAL GO / NO-GO
```

If CONDITIONAL GO, list the conditions.

---

## 6. Autonomy boundaries

You MAY, without further confirmation:
- Read any file in the repository.
- Run read-only diagnostic commands (`git`, `grep`, `find`, `cat`, `ls`, `php artisan --version`, `npx playwright --version`).
- Run a targeted Playwright test rerun for diagnostic purposes.
- Write the report file listed above.

You MAY NOT without explicit user confirmation:
- Modify application source code.
- Modify test-harness source code.
- Run database migrations or seeders.
- Run `git commit`, `git push`, `git reset`, or any git mutation.
- Install or remove packages.
- Delete files.

---

## 7. Completion criteria

Stop and report success when:
1. All Phase 1–4 questions are answered.
2. The report file is written.
3. A deployment verdict is issued.

If you hit a blocker (e.g., isolation log missing, Playwright not installed, repo in unexpected state), state the blocker explicitly and ask the user for the missing input rather than guessing.

---

*Template version: 2026-06-13*
*Derived from: CREAMS UAT Recovery & Deployment Certification Mission*
