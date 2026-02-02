---
name: route-audit
description: Audits CREAMS web.php routes against controller methods. Finds broken route-to-method mappings that cause 500 errors at runtime, dead imports, and shadowed routes. Use when checking route integrity or after any route/controller changes.
disable-model-invocation: true
allowed-tools: Read, Grep, Glob
context: fork
agent: Explore
---

# CREAMS Route Audit

Systematically verify every route in `routes/web.php` maps to an existing controller method.

## Known Facts
- Auth pattern: session-based (`session('id')`, `session('role')`) alongside Laravel `auth` middleware
- Route shadowing: Laravel uses first-match routing — duplicate URLs resolve to whichever is defined first
- Controllers live under `app/Http/Controllers/` with subdirectories for namespaced controllers (Centre/, Letters/, Profile/, Dashboard/, etc.)

## Step 1 — Extract All Routes
Read `routes/web.php` in full. For each route definition, extract:
- HTTP method (GET, POST, PUT, DELETE)
- URL pattern
- `Controller::class` reference
- Method name
- Route name (if named)
- Line number in web.php

## Step 2 — Extract All Controller Methods
For each unique controller referenced in Step 1:
1. Resolve its full file path (check subdirectories)
2. Read the file
3. Grep for all `public function` declarations
4. Record: method name → line number

## Step 3 — Cross-Reference
Compare Step 1 against Step 2. Categorize every finding:

| Status | Definition | Action |
|--------|------------|--------|
| **BROKEN** | Route references a method that does not exist | Will 500 at runtime — must fix |
| **DEAD IMPORT** | `use` statement in web.php for a controller never referenced in any route | Remove the import |
| **SHADOWED** | Two routes handle the same URL — second one is unreachable | Remove or rename |
| **OK** | Route maps correctly | No action needed |

## Step 4 — Output
Present findings as a table:

```
| Status  | URL                        | Controller::method              | web.php line | Controller line |
|---------|----------------------------|---------------------------------|--------------|-----------------|
| BROKEN  | GET /admin/letters         | LetterController::index         | 836          | —               |
| DEAD    | (import)                   | ProfileLetterController         | 50           | —               |
| OK      | GET /iep                   | IepController::index            | 373          | 117             |
```

## Already-Fixed Items (skip these)
- `/letters/generate` shadow — replaced with comment (line ~211)
- `/letters-old` and `/letters-old/create` — converted to redirects
- Admin legacy letter block (`admin.letters.*`) — removed, replaced with redirect
- Unused imports: `LetterController`, `ProfileLetterController` — removed
