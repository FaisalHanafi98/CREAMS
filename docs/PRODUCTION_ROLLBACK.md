# Production rollback procedure

## When to rollback

- Application errors after deployment that affect user workflows
- Migration failure that leaves the database in a broken state
- Performance degradation that makes the system unusable

## Pre-requisites

- SSH access to the Lightsail instance (`ec2-user@<LIGHTSAIL_IP>`)
- The previous working commit hash (check GitHub or `git log` on server)

## Quick rollback (no migration involved)

```bash
ssh ec2-user@<LIGHTSAIL_IP>

cd /var/www/creams-prod

# Enable maintenance mode
php artisan down --message="Rolling back — back shortly" --retry=60

# Revert to last known good commit
git fetch origin main
git log --oneline -5              # identify the target commit
git reset --hard <GOOD_COMMIT>

# Rebuild
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart PHP-FPM and bring app back up
sudo systemctl reload php-fpm
php artisan up
```

## Rollback with migration revert

If the failed deployment included a migration:

```bash
# 1. Check which migrations ran
php artisan migrate:status

# 2. Rollback the last batch
php artisan migrate:rollback --step=1

# 3. Verify the rollback
php artisan migrate:status

# 4. Then proceed with the quick rollback steps above
```

If the migration's `down()` method is missing or broken, restore the database from backup instead:

```bash
# Restore from the most recent automated backup
mysql -u creams_user -p creams_prod < /var/backups/creams/latest.sql
```

## Database backup schedule

Automated daily backups should be configured via cron on the Lightsail instance:

```cron
0 2 * * * mysqldump -u creams_user -p'<DB_PASSWORD>' creams_prod | gzip > /var/backups/creams/creams_$(date +\%Y\%m\%d).sql.gz
```

Retain at least 7 days of backups. Verify backups are running with `ls -la /var/backups/creams/`.

## Post-rollback

1. Notify stakeholders that a rollback occurred
2. Document what went wrong in a post-mortem (even a short one)
3. Fix the root cause on a feature branch, not directly on main
4. Re-deploy through the normal CI/CD pipeline once fixed

## Emergency contacts

| Role | Who |
|------|-----|
| System admin | Check project Slack or team channel |
| Database admin | Same as system admin for this project |
