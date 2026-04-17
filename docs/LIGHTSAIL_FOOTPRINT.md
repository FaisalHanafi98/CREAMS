# CREAMS Lightsail Resource Footprint

**Document Type**: Deployment Planning Reference
**Last Updated**: 2026-04-17
**Purpose**: Record current resource usage so the Portfolio co-tenancy planning session can design the traffic strategy for sharing the $5 Lightsail instance.
**Status**: READ ONLY — no config changes made in this session.

---

## Target Instance

- **Plan**: Amazon Lightsail $5/month
- **RAM**: 512 MB
- **vCPU**: 2 (burst)
- **SSD**: 20 GB
- **Data transfer**: 1 TB/month
- **Co-tenants**: CREAMS + Portfolio (Spring Boot/React)

---

## Application Stack (as measured 2026-04-17)

| Component | Version | Notes |
|-----------|---------|-------|
| PHP | 8.5.4 | CLI version — prod server will use 8.1+ |
| Laravel | 10.48.29 | — |
| Database driver | MySQL | DB name: `cream` (dev), target: new DB on Lightsail |
| Session driver | `file` | `storage/framework/sessions/` |
| Cache driver | `file` | No Redis/Memcached needed |
| Queue | `sync` | No background worker process needed |
| Web server | nginx (target) | Current: Laravel dev server locally |

---

## Memory Profile

### PHP

| Setting | Current (dev) | Target (prod) |
|---------|--------------|--------------|
| `memory_limit` | 128M | 128M (keep) |
| PHP-FPM `pm` | n/a (Windows dev) | `pm = ondemand` (recommended for low-traffic) |
| PHP-FPM workers | n/a | 2–3 max children |
| Per-worker memory | ~40–80 MB typical for Laravel | Estimate 60MB average |

**PHP-FPM estimated peak**: 3 workers × 80MB = ~240 MB

### MySQL

| Setting | Estimate |
|---------|---------|
| InnoDB buffer pool | Target: 64–128 MB for 512MB instance |
| Current data size | Run `SELECT table_schema, ROUND(SUM(data_length+index_length)/1024/1024,1) AS MB FROM information_schema.TABLES WHERE table_schema='cream' GROUP BY table_schema;` on prod DB to measure |
| Tables | 50+ (see `DATABASE_SCHEMA_DOCUMENTATION.md`) |

**MySQL estimated overhead**: ~100–150 MB at idle

### Total Memory Estimate

| Component | Estimated MB |
|-----------|-------------|
| PHP-FPM (3 workers) | 240 |
| MySQL | 128 |
| nginx | 20 |
| OS overhead | 60 |
| **Total** | **~448 MB** |

**Verdict**: Very tight on 512 MB. Portfolio (Spring Boot JVM) will need additional RAM. Recommend Portfolio session assess whether co-tenancy on $5 is viable or if upgrade to $10 (2 GB) is needed.

---

## Disk Profile

### Logs (critical — action needed before deploy)

| Channel | Path | Retention | Level (default) | Size (dev, 2026-04-17) |
|---------|------|-----------|----------------|----------------------|
| laravel (daily) | `storage/logs/laravel-*.log` | 14 days | **debug** | ~included in 495 MB total |
| database | `storage/logs/database-*.log` | 30 days | warning | — |
| security | `storage/logs/security-*.log` | **90 days** | info | — |
| application | `storage/logs/application-*.log` | 14 days | debug | — |
| **Total log storage** | `storage/logs/` | — | — | **495 MB** |

**Warning**: 495 MB of logs accumulated in development. In production on a 20 GB disk, this retention policy will fill the disk within weeks if `LOG_LEVEL` stays at `debug`.

**Required before deploy**:
1. Set `LOG_LEVEL=warning` in `.env.production` (also resolves PRE_DEPLOY_SECURITY_CHECKLIST B2)
2. Reduce `security` channel retention from 90 days to 30 days in `config/logging.php`
3. Set up logrotate on the Lightsail server for `storage/logs/` (compress + delete older than retention period)
4. Do NOT deploy the dev log files — `storage/logs/*.log` should be gitignored and empty on first deploy

### Sessions

| Metric | Value |
|--------|-------|
| Driver | `file` (stored in `storage/framework/sessions/`) |
| Current session count (dev) | 1,041 files |
| Current size | 2.4 MB |
| Session lifetime | Check `config/session.php` `lifetime` value |

File-based sessions are fine for low-traffic single-server deployment. No Redis needed.

### Source Code

| Component | Size (approx) |
|-----------|-------------|
| App source (excl. vendor, .git) | ~50–100 MB (full dir is 2.9 GB including 495 MB logs and test node_modules) |
| `vendor/` (Composer dependencies) | 109 MB (measured 2026-04-17) |
| `node_modules/` | Not present (compiled assets only) |
| Compiled assets (`public/`) | Small (CSS/JS bundles) |

**Deployment note**: `vendor/` should be installed on the server via `composer install --no-dev`, not committed to git. On Lightsail, this is a one-time action after pulling the repo.

---

## Log Rotation Action Items (pre-deploy)

```bash
# Example logrotate config for /etc/logrotate.d/creams
/var/www/creams/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 664 www-data www-data
}
```

---

## Recommended `.env.production` Log Settings

```env
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

Setting `LOG_LEVEL=warning` cuts all channels to warning-and-above regardless of their individual defaults. This eliminates the 495 MB dev-log problem in production.

---

## Co-Tenancy Notes for Portfolio Session

| Item | CREAMS value | Impact on Portfolio planning |
|------|-------------|------------------------------|
| PHP-FPM peak memory | ~240 MB | Portfolio (Spring Boot) will need the remaining ~250 MB — tight |
| MySQL memory | ~128 MB | Shared MySQL instance possible if Portfolio uses a separate DB |
| Log disk usage | 495 MB (dev) → target <50 MB production | Keep `LOG_LEVEL=warning` |
| Queue | sync (no background worker) | No resource contention from queue worker |
| Cron jobs | Not yet set up | Will need scheduler entry for session GC and log cleanup |
| Traffic | Low (PPDK internal tool, ~5-20 concurrent users) | Portfolio traffic pattern will dominate |
