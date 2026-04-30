# CREAMS — Staging Seed Policy

**Document type**: Deployment policy
**Scope**: Lightsail staging, UAT instances, demo deployments
**Status**: ENFORCED
**Last updated**: 1 May 2026

---

## Hard rule

**Staging, UAT, and demo environments may only run `UATSeeder`.**
**They must never run `db:seed` (the default chain) and must never run `IRLSeeder`.**

```
# ALLOWED on staging/UAT/demo:
php artisan db:seed --class=UATSeeder

# FORBIDDEN on staging/UAT/demo:
php artisan db:seed                    # runs the full DatabaseSeeder chain
php artisan db:seed --class=IRLSeeder  # contains real PDPA data
```

## Why

| Seeder | What it contains | Allowed in non-local? |
|---|---|---|
| `UATSeeder` | Faker-only anonymised data: 3 fake centres (UA1/UA2/UA3), 16 fake staff, 21 fake trainees, 9 fake activities. State code `99` IC numbers (unused by real Malaysian scheme). | YES — staging/UAT/demo |
| `IRLSeeder` | Real production data for Gombak centre. Contains real Malaysian IC numbers, real names, real addresses, real medical conditions. PDPA-protected. | NO — local development only |
| `DatabaseSeeder` (default chain) | Calls 9 seeders including `TestingGuideDataSeeder` which has 3 literal IC numbers. Mixed PDPA exposure. | NO — local development only |

## Enforcement layers

There are three independent guards. Any one is sufficient. Together they fail closed.

### Layer 1 — Code guard (hard)

`IRLSeeder::run()` checks `app()->environment()` and throws `RuntimeException` if the env is anything other than `local`. It also refuses to run with `APP_DEBUG=false` (production posture detection).

This means even if a deploy script accidentally invokes `db:seed --class=IRLSeeder` on staging, the seeder aborts before touching the database.

### Layer 2 — Deployment script (hardcoded)

The Lightsail deploy script (`docs/04_Deployment_Guides/STAGING_DEPLOY_RUNBOOK.md` once written) hardcodes the seed step as:

```
php artisan db:seed --class=UATSeeder --force
```

No variable substitution, no env-var-driven seeder selection. The seeder is named in the literal command.

### Layer 3 — `.env.staging` file (defence in depth)

Staging environments set:

```
APP_ENV=staging
APP_DEBUG=false
```

Both are checked by the IRLSeeder code guard. Either one alone is enough to block.

## What to do if you need real data on staging

Don't. The cases that look like "I need real data on staging" are usually:

- **Reproducing a production bug**: copy the bug report's anonymised steps into UATSeeder. Add a fixture row that mirrors the broken shape without the real PII.
- **Stakeholder demo wanting "realistic" data**: Faker can produce realistic-looking centre/trainee data without using real records. UATSeeder already does this.
- **Performance testing**: spawn a separate seeder (`PerfSeeder`) that produces 10,000 anonymised rows. Never IRLSeeder.

If you genuinely cannot avoid real data on a staging instance (e.g., a one-off migration validation), do all of the following:

1. Get explicit written approval from the project owner.
2. Use a brand-new throwaway Lightsail instance — not the shared staging.
3. Lock the instance behind HTTP Basic Auth + IP allowlist + robots disallow.
4. Document the start time, end time, and reason in `docs/audit/`.
5. Destroy the instance and its database snapshot within 24 hours.
6. Confirm destruction with `aws lightsail get-instances` showing it's gone.

## Related

- `database/seeders/UATSeeder.php` — the only sanctioned seeder for non-local environments
- `database/seeders/IRLSeeder.php` — contains the env guards described in Layer 1
- `docs/audit/pdpa_scan_2026-05-01.log` — audit baseline
- `docs/audit/git_history_audit_2026-05-01.log` — full-history scan results

---

*Created: 1 May 2026 — sprint Day 3*
*Trigger: stakeholder confirmation that IRLSeeder contains real production data, not test fixtures.*
