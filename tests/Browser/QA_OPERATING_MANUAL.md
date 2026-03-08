# Claude QA Operating Manual (v1.0)

## Authority Rules

### Claude MAY NOT:
- Refactor backend logic without evidence
- Change UI structure unless explicitly instructed
- Guess selectors without inspecting HTML or screenshots

### Claude MAY:
- Adjust Playwright selectors
- Disable or bypass client-side JS validation
- Recommend seed fixes or migration steps

---

## Investigation Protocol (NON-NEGOTIABLE)

Claude MUST follow this order:

### 1. Summarise Observed Failure
- URL
- Error message
- Screenshot/video evidence

### 2. State Hypothesis
- Route issue
- Selector mismatch
- Middleware / permission
- Client-side JS interference
- Seed/data inconsistency

### 3. Propose Minimal Fix
- One change at a time
- Explain why this fix addresses the hypothesis

### 4. Stop
- Await confirmation before next fix

---

## Test Philosophy Rules

1. Prefer existence checks over exact text
2. Prefer role-based locators over CSS selectors
3. Avoid asserting table structure unless UI frozen
4. Performance failures never block functional fixes

---

## Evidence Requirement Rule

Claude may only debug if at least ONE exists:
- Screenshot
- Video
- Trace
- Laravel log snippet

**No evidence = no fix.**

---

## Token-Saving Rules

1. Do NOT paste entire test files repeatedly
2. Use diffs or line references
3. Reference artefact paths instead of descriptions
4. Solve ONE root cause per session

---

## Failure Classification Order

Address categories in this order:
1. **Environment / Seed** - Database state issues
2. **Route / Middleware** - Access control problems
3. **Client-side JS** - Validation interference
4. **Selector mismatch** - Wrong element references
5. **Performance** - Timing issues

---

## Output Format for Every Response

```
1. Failure Summary
   - URL: [url]
   - Error: [message]
   - Evidence: [path to screenshot/video/trace]

2. Evidence Referenced
   - [List of artefacts used]

3. Root Cause Hypothesis
   - Category: [Environment/Route/JS/Selector/Performance]
   - Explanation: [Why this is the likely cause]

4. Minimal Fix Proposal
   - File: [path]
   - Change: [description]
   - Rationale: [Why this addresses the hypothesis]

5. Next Validation Step
   - [Command to run or action to take]
```

---

## Stop Conditions

- Do not continue after proposing a fix
- Await confirmation or new artefacts before proceeding

---

## E2E Testing Mode

The test suite injects `window.__E2E_TESTING__ = true` on every page.

Client-side code can check for this:
```javascript
if (window.__E2E_TESTING__) {
  // Skip validation or return early
  return true;
}
```

---

## Trace Viewer Workflow

When something fails:
```bash
npx playwright show-trace test-results/**/trace.zip
```

---

## Artefact Structure

```
test-results/
├── results.json
├── playwright-report/
├── test-name/
│   ├── video.webm
│   ├── trace.zip
│   └── screenshot.png
```

---

*Version 1.0 - 2026-01-27*
