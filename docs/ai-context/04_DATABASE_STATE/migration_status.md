# CREAMS — Migration Status

**Last updated**: 2026-05-08
**Evidence**: `php artisan migrate:status` output from local dev DB + server confirmation

---

## Local development database (cream)

| State | Count |
|---|---|
| Total migrations | 34 |
| Ran | 34 |
| Pending | 0 |

All migrations have run. No pending migrations.

---

## Server database (creams_app on 54.169.32.54)

| State | Count |
|---|---|
| Total migrations | 34 |
| Ran | 34 (VERIFIED by server output 2026-05-07) |
| Pending | 0 |

---

## Migration timeline summary

| Date range | Migrations | Purpose |
|---|---|---|
| 2019-12 | 1 | Personal access tokens |
| 2025-01 | 7 | Foundation, client, service, attendance, asset, communication, system |
| 2025-09 | 10 | Phone format, jobs, audit logs, volunteers, activities, enrollments, FKs, assets, sessions |
| 2025-10 | 4 | Activity logs, public holidays, states, centres |
| 2026-01 | 4 | FK constraints, performance indexes, soft deletes, activity schedule templates |
| 2026-02 | 3 | IEP tables, notifications, schema mismatches |
| 2026-03 | 2 | Trainee columns, trainee audit logs |
| 2026-04 | 1 | Asset movements restore |

---

## Known migration risks

1. **Trigger creation**: Migration `2025_09_29_140000` creates MySQL triggers. Requires `log_bin_trust_function_creators = 1` in MySQL config (already set on server via `/etc/my.cnf`). Fresh setups must set this before migrating.

2. **TEXT column defaults**: Migration `2025_09_29_140000` originally tried `DEFAULT ''` on a TEXT column — MySQL disallows this. Fixed to `nullable()` in commit `09f0c99`.

3. **Phone column NOT NULL**: Migration `2025_09_29_120000` inserts a fallback admin row without `phone`. Fixed in commit `09f0c99`.

4. **2026_01_30_165801**: This migration adds FK constraints to several tables. If tables were partially created in a different order, FK references can fail. Safe as long as all foundation migrations ran first.

---

## Running migrations on a fresh server

```bash
# Set trust variable before any migrations (triggers)
mysql -u root -p -e "SET GLOBAL log_bin_trust_function_creators = 1;"
echo "[mysqld]
log_bin_trust_function_creators = 1" | sudo tee -a /etc/my.cnf

# Run migrations
php artisan migrate --force

# Verify
php artisan migrate:status | grep "No" | wc -l  # Should be 0
```

---

## Do not run

- `php artisan migrate:fresh` on any production or staging database
- `php artisan migrate:rollback` without human review of what will be rolled back
