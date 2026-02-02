---
name: fix-verify
description: Applies a CREAMS code fix with mandatory before/after Playwright screenshot verification. Use whenever making route, controller, or view changes that affect page rendering. Never apply a fix without visual proof.
disable-model-invocation: true
argument-hint: [description of what to fix]
allowed-tools: Read, Edit, Bash
---

# CREAMS Fix & Verify

Screenshot-gated workflow. Every fix gets a before screenshot (showing the broken state) and an after screenshot (confirming the fix).

## Credentials
- URL: `http://localhost:8000`
- Email: `admin@creams.system`
- Password: `admin123`

## Phase 1 — Server Check
Verify Laravel dev server is running:
```bash
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000
```
If not 200, start it:
```bash
cd CREAMS && php artisan serve --port=8000
```
Laravel hot-reloads route/controller changes — no restart needed after edits.

## Phase 2 — Session Check
Navigate to `http://localhost:8000/login`. If redirected to `/dashboard`, you're already logged in. If login page shows, submit the credentials above.

## Phase 3 — Before Screenshot
1. Navigate to the URL that is currently broken
2. Confirm the error state (e.g., page title contains the exception class, Ignition error page renders)
3. Take a Playwright screenshot — label it clearly as BEFORE
4. Note the exact error: exception class + message

## Phase 4 — Apply Fix
5. Read the file to be edited (never edit blind)
6. Apply the minimal change needed
7. Do NOT restart the server

## Phase 5 — After Screenshot
8. Navigate to the same URL again
9. Confirm success criteria:
   - No Ignition error page
   - Page title is the intended view title (not an exception message)
   - HTTP status 200 (or 302 redirect to a working page)
10. Take a Playwright screenshot — label it clearly as AFTER

## Phase 6 — Summary
Present to the user:
- BEFORE: URL → error type observed
- AFTER: URL → working page confirmed
- What was changed and why

## Anti-Patterns
- ❌ Applying a fix and assuming it works without navigating to verify
- ❌ Restarting the server between edit and verification (unnecessary, wastes time)
- ❌ Testing a different URL than the one that was broken
- ❌ Skipping the before screenshot ("we know it's broken") — the user needs to see both
