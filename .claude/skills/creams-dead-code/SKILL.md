---
name: dead-code
description: Finds and removes dead code in CREAMS — unused imports in web.php, unreferenced controllers, and dead route blocks. Run periodically or after route/controller changes to keep the codebase clean.
disable-model-invocation: true
allowed-tools: Read, Grep, Glob, Edit
---

# CREAMS Dead Code Sweep

Three-pass sweep: unused imports → unreferenced controllers → dead routes.

## Pass 1 — Unused Imports in web.php

1. Read `routes/web.php`
2. Extract every `use App\...` statement (class name + line number)
3. For each imported class, grep web.php for `ClassName::class`
4. If zero matches → the import is dead

**Action**: Remove dead imports. They add noise and confuse future readers.

**Example of what was caught:**
- `use App\Http\Controllers\Profile\LetterController as ProfileLetterController;` — imported but never referenced in any route. Removed.
- `use App\Http\Controllers\LetterController;` — became dead after the admin legacy letter routes block was removed. Removed.

## Pass 2 — Unreferenced Controllers

1. Glob all `.php` files under `app/Http/Controllers/` recursively
2. For each file, extract the class name from the `class ClassName` declaration
3. Grep `routes/web.php` for that class name
4. If zero matches → controller is never routed

**Action**: Flag these for user review. Do NOT delete without confirmation. Some may be referenced by other controllers or used as base classes.

## Pass 3 — Dead Route Blocks

A route block is dead if ALL of its routes reference methods that don't exist on the target controller.

1. For each route group or individual route, check method existence (use findings from Pass 1 if a route audit was already run)
2. If every method in a block is missing → the entire block is dead
3. Determine replacement strategy:
   - If there's a logical successor (e.g., a modern replacement module), replace with a redirect closure
   - If there's no successor, remove entirely

**Example of what was done:**
```php
// REMOVED: 8 routes all pointing to non-existent LetterController methods
// REPLACED WITH:
Route::get('/letters', function () {
    return redirect()->route('letters.modern.index');
})->name('admin.letters.index');
```

## Output Format

Present findings in three sections:

```
## REMOVED
- [what] — [why]

## FLAGGED (needs your decision)
- [what] — [why it might be dead] — [risk if deleted]

## SKIPPED
- [what] — [why it's fine]
```

## Rules
- Never delete a controller file without user confirmation
- Always verify the route file compiles after removing routes (`php artisan route:list` should not error)
- Redirects are preferred over hard deletion for routes that users might have bookmarked
