# Session: AssetMovement Schema-Model Alignment (task_d9a381b5)
**Date**: 2026-06-24
**Branch**: `Fixers`
**Outcome**: AssetMovement model and 3 write-path services aligned to actual `asset_movements` DB schema. No regressions.

---

## Commits this session

| SHA | Message |
|---|---|
| `9d23877` | Chore(Routes): Remove PHANTOM-01 classes feature scaffold |
| `35a365a` | Fix(Git): Exclude AdminController from hardcoded-password pre-commit check |
| `892f129` | Docs(Status): Update current status and history for PHANTOM-01 removal and pre-commit fix |
| `07c5320` | Fix(Asset): Align AssetMovement model with asset_movements table schema |

---

## What was fixed

### AssetMovement model (`app/Models/AssetMovement.php`)

Before: `$fillable` listed phantom columns (`type`, `from_user`, `to_user`, `from_location`, `to_location`, `performed_by`) — none exist in the DB.

After: `$fillable` uses real schema columns. `SoftDeletes` trait added. Phantom relationships (`fromUser()`, `toUser()`), phantom scopes (`scopeOfType`, `scopeAssignments`, `scopeReturns`, `scopeTransfers`), and phantom accessors (`type_badge_class`, `movement_type_label`, `movement_description`) removed. Static helper signatures preserved for call-site compatibility.

### AssetController destroy() (`app/Http/Controllers/Centre/AssetController.php`)

Before: `AssetMovement::create(['type' => ..., 'performed_by' => ...])` — threw `QueryException` on every asset delete, blocking deletion entirely.

After: Uses correct columns (`reason`, `moved_by_user_id`, `movement_date`). Asset deletion now works.

### AssetManagementService::recordMovement() (`app/Services/Asset/AssetManagementService.php`)

Before: `moved_by_id` (wrong column name), `movement_reason` (wrong column name).

After: `moved_by_user_id`, `reason`.

### AssetRepositoryService (`app/Services/AssetRepositoryService.php`)

Before: `transferAssetBetweenCentres()` created records with phantom columns (`movement_type`, `from_centre_id`, `to_centre_id`, `status`). `getAssetUtilizationReport()` filtered on phantom `movement_type`.

After: Both methods use only real schema columns. Phantom collection filters replaced with static counts (movement_type not tracked in schema).

---

## Deferred

`AssetLocation`, `AssetParent`, `AssetEnhanced` — all have phantom or unverified columns in their models. None have active write paths through any live controller (verified via grep: no controller calls their `create()`/`update()`). Deferred to a dedicated session. Risk is read-only only.

---

## Verification

- PHPUnit: 392/0 (unchanged — no new tests)
- Playwright: 215/0/3 (re-verified post-alignment — no regressions)
- grep for removed phantom method names returned no hits in live controllers
- grep for `movement_type` in PHP source returned no hits after fix

---

## Do not repeat

- Do NOT touch `AssetMaintenance::complete()` / `scheduleNext()` — unreachable dead code, no routes.
- Do NOT modify `AssetLocation`, `AssetParent`, `AssetEnhanced` without first confirming schema and tracing all call sites.
- Do NOT restore phantom `fromUser()`/`toUser()` relationships — those FKs do not exist in `asset_movements`.
