# Test Suite Baseline

**Document Type**: Quality Gate Reference
**Last Updated**: 2026-04-25
**Rule**: The numbers in this document are a FLOOR. No session may leave the test suite below these counts. Regressions must be investigated before committing.

---

## Baseline as of 2026-04-25

| Metric | Count |
|--------|-------|
| **Tests passed** | **359** |
| **Assertions** | **520** |
| **Failures** | **0** |
| **Errors** | **0** |
| **Skipped** | **0** |
| **Test files** | **37** |
| **Duration (approx)** | ~28-70s (varies by machine / DB state) |

Command used to capture:

```bash
php artisan test --env=testing
```

---

## Test File Inventory (35 files)

```
tests/Feature/
├── Activity/         ActivityManagementTest.php
├── Asset/            AssetManagementTest.php
├── Auth/             AuthenticationTest.php
├── CentreIsolation/  CentreIsolationTest.php
├── Dashboard/        DashboardTest.php
├── IEP/              IEPManagementTest.php
├── Letter/           LetterManagementTest.php
├── RBAC/             AdminAccessTest.php
│                     CentreScopeIsolationTest.php
│                     RoleAccessTest.php
├── Report/           ReportTest.php
├── Security/         MessageCentreIsolationTest.php          ← Added 2026-04-17
│                     AssetMaintenanceCentreIsolationTest.php ← Added 2026-04-25
│                     AssetMovementCentreIsolationTest.php    ← Added 2026-04-25
├── SoftDelete/       SoftDeleteTest.php
├── Staff/            StaffManagementTest.php
├── Trainee/          TraineeManagementTest.php
└── Volunteer/        VolunteerTest.php

tests/Unit/
├── Models/           ActivityTest.php
│                     CentreTest.php
│                     TraineeTest.php
│                     UserTest.php
└── Rules/            (various)
```

---

## History

| Date | Tests | Assertions | Notes |
|------|-------|------------|-------|
| 2026-02-07 | ~329 | n/a | Prior baseline (from CLAUDE.md — approximate, pre-security hardening) |
| 2026-04-17 | **347** | **502** | Post-security hardening. Added `MessageCentreIsolationTest` (4 tests, 8 assertions). |
| 2026-04-25 | **359** | **520** | CentreScope gap closure. Added `AssetMaintenanceCentreIsolationTest` (6 tests) and `AssetMovementCentreIsolationTest` (6 tests). Restored `asset_movements` table in test DB via new migration. |

---

## Coverage

PHPUnit test coverage is not currently tracked (no `--coverage` flag in CI). The target from `CLAUDE.md` is 60%. Current estimate based on file count is approximately 15-20% (up from the 13% baseline in Feb 2026 due to new security test files added in April).

Coverage tooling setup is a separate task and should not block deployment.
