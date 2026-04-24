# CREAMS - Production Deployment Guide

**Last Updated:** 2026-02-06
**Version:** 1.0.0
**Security Phase:** Phase 1 - Security Hardening

---

## Table of Contents

1. [Production Security Checklist](#production-security-checklist)
2. [Laravel Ignition Configuration](#laravel-ignition-configuration)
3. [Environment Configuration](#environment-configuration)
4. [Server Requirements](#server-requirements)
5. [Deployment Steps](#deployment-steps)
6. [Post-Deployment Verification](#post-deployment-verification)
7. [Security Headers](#security-headers)
8. [Monitoring & Logging](#monitoring--logging)

---

## Production Security Checklist

Before deploying to production, ensure ALL items are completed:

### Critical Security Items

- [ ] **APP_DEBUG is set to `false`** in `.env`
- [ ] **APP_ENV is set to `production`** in `.env`
- [ ] **APP_KEY has been generated** using `php artisan key:generate`
- [ ] **All debug routes removed** from `routes/web.php`
- [ ] **SSL/TLS certificate installed** (HTTPS enabled)
- [ ] **Database credentials are strong** (20+ character passwords)
- [ ] **Session cookies are secure** (`SESSION_SECURE_COOKIE=true`)
- [ ] **Session encryption enabled** (`SESSION_ENCRYPT=true`)
- [ ] **Rate limiting configured** on all auth routes
- [ ] **File permissions set correctly** (755 for directories, 644 for files)
- [ ] **Storage directory writable** by web server
- [ ] **Sensitive files protected** (.env, composer.json not web-accessible)

### Laravel Ignition Security

- [ ] **Verify Ignition is disabled** (check error pages show generic errors)
- [ ] **No stack traces visible** on error pages
- [ ] **Error logging configured** to file/service, not screen
- [ ] **Log level set to `warning` or `error`** (not `debug`)

### Infrastructure Security

- [ ] **Firewall configured** (only ports 80, 443 open)
- [ ] **Database not exposed** to internet
- [ ] **Redis password protected** (if using Redis)
- [ ] **SSH key-based authentication** only (no password login)
- [ ] **Fail2ban or similar** installed and configured
- [ ] **Automated backups** configured (database + files)
- [ ] **Monitoring alerts** configured

---

## Laravel Ignition Configuration

### What is Laravel Ignition?

Laravel Ignition is the error page framework that provides detailed debugging information during development. In production, this MUST be disabled to prevent:

- **Information disclosure** (stack traces, environment variables)
- **Code path revelation** (file paths, class names, line numbers)
- **Security vulnerability exposure** (package versions, framework internals)

### How Ignition is Controlled

#### 1. Composer Configuration

Ignition is installed as a **dev dependency** in `composer.json`:

```json
"require-dev": {
    "spatie/laravel-ignition": "^2.0"
}
```

**For production:**
```bash
# Install WITHOUT dev dependencies
composer install --no-dev --optimize-autoloader
```

This ensures Ignition is NOT loaded in production.

#### 2. Environment Configuration

In `config/app.php`, debug mode is controlled by:

```php
'debug' => (bool) env('APP_DEBUG', false),
```

**Production `.env` MUST have:**
```env
APP_DEBUG=false
APP_ENV=production
```

#### 3. Verification

Test that Ignition is disabled by triggering an error:

1. Visit a non-existent route (e.g., `/this-does-not-exist`)
2. **Expected behavior (CORRECT):** Generic 404 error page with no stack trace
3. **Incorrect behavior (DANGER):** Detailed Ignition error page with code snippets

If you see the detailed error page in production, **IMMEDIATELY**:
1. Set `APP_DEBUG=false` in `.env`
2. Run `php artisan config:clear`
3. Verify again

---

## Environment Configuration

### Production .env File

Use the `.env.production` template provided:

```bash
cp .env.production .env
```

Then configure ALL required values:

#### Critical Settings

```env
# MUST BE SET CORRECTLY
APP_NAME="CREAMS"
APP_ENV=production              # ← CRITICAL: Must be "production"
APP_DEBUG=false                 # ← CRITICAL: Must be false
APP_URL=https://your-domain.com # ← CRITICAL: Must use HTTPS

# Generate a new key
APP_KEY=                        # ← Run: php artisan key:generate
```

#### Database Settings

```env
DB_CONNECTION=mysql
DB_HOST=your-production-db-host
DB_PORT=3306
DB_DATABASE=cream_production
DB_USERNAME=cream_user
DB_PASSWORD=STRONG_PASSWORD_HERE  # ← 20+ characters, mixed case, numbers, symbols
```

#### Session & Cache (Recommended: Redis)

```env
# For better performance and scalability
SESSION_DRIVER=redis
SESSION_ENCRYPT=true              # ← CRITICAL: Encrypt session data
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
SESSION_SECURE_COOKIE=true        # ← CRITICAL: Requires HTTPS

CACHE_DRIVER=redis
CACHE_PREFIX=creams_prod
```

#### Logging

```env
LOG_CHANNEL=daily                 # ← Auto-rotate logs daily
LOG_LEVEL=warning                 # ← Only log warnings and errors (not debug info)
```

#### Security Settings

```env
RATE_LIMIT_LOGIN=3                # ← Stricter than development (was 5)
RATE_LIMIT_API=30                 # ← Stricter than development (was 60)
PASSWORD_MIN_LENGTH=12            # ← Enforce strong passwords
```

---

## Server Requirements

### Minimum Requirements

- **PHP:** 8.1 or higher
- **MySQL:** 8.0 or higher (or MariaDB 10.3+)
- **Composer:** 2.x
- **Web Server:** Nginx or Apache with mod_rewrite
- **SSL Certificate:** Valid SSL/TLS certificate (Let's Encrypt recommended)

### PHP Extensions Required

```bash
php -m | grep -E 'pdo|mysql|mbstring|openssl|tokenizer|xml|ctype|json|bcmath|fileinfo|gd'
```

All of these MUST be installed and enabled.

### Recommended Server Setup

- **OS:** Ubuntu 22.04 LTS or newer
- **Web Server:** Nginx 1.18+
- **PHP:** PHP 8.2-FPM
- **Database:** MySQL 8.0 (dedicated server or RDS)
- **Cache:** Redis 6.x or higher
- **Queue Worker:** Supervisor (for background jobs)

---

## Deployment Steps

### Step 1: Server Preparation

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server redis-server php8.2-fpm \
    php8.2-mysql php8.2-redis php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-gd php8.2-zip php8.2-bcmath \
    composer certbot python3-certbot-nginx
```

### Step 2: Clone Repository

```bash
# Navigate to web directory
cd /var/www

# Clone repository (use deployment key)
git clone git@github.com:your-org/creams.git
cd creams

# Checkout production branch
git checkout main
```

### Step 3: Install Dependencies

```bash
# Install PHP dependencies (WITHOUT dev packages)
composer install --no-dev --optimize-autoloader

# Set correct permissions
sudo chown -R www-data:www-data /var/www/creams
sudo chmod -R 755 /var/www/creams
sudo chmod -R 775 /var/www/creams/storage
sudo chmod -R 775 /var/www/creams/bootstrap/cache
```

### Step 4: Configure Environment

```bash
# Copy production environment file
cp .env.production .env

# Generate application key
php artisan key:generate

# Edit .env with production values
nano .env
```

**IMPORTANT:** Configure ALL values in `.env` before proceeding.

### Step 5: Database Setup

```bash
# Run migrations
php artisan migrate --force

# Seed initial data (if needed)
php artisan db:seed --force --class=ProductionSeeder
```

### Step 6: Cache Configuration

```bash
# Cache routes, config, views for performance
php artisan route:cache
php artisan config:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Step 7: Configure Web Server

#### Nginx Configuration

Create `/etc/nginx/sites-available/creams`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    root /var/www/creams/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';" always;

    # Deny access to sensitive files
    location ~ /\.env {
        deny all;
    }

    location ~ /composer\.(json|lock) {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/creams /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 8: SSL Certificate

```bash
# Obtain Let's Encrypt certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### Step 9: Queue Worker (if using queues)

Create `/etc/supervisor/conf.d/creams-worker.conf`:

```ini
[program:creams-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/creams/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/creams/storage/logs/worker.log
stopwaitsecs=3600
```

Start queue workers:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start creams-worker:*
```

---

## Post-Deployment Verification

### 1. Verify Ignition is Disabled

```bash
# Test 1: Visit non-existent route
curl -I https://your-domain.com/nonexistent-route

# Expected: 404 error with NO detailed stack trace
# If you see a detailed error page, CHECK APP_DEBUG immediately
```

### 2. Verify APP_DEBUG is False

```bash
# Check current configuration
php artisan tinker
>>> config('app.debug')
=> false  // ← MUST be false

>>> config('app.env')
=> "production"  // ← MUST be "production"
```

### 3. Check Error Logging

```bash
# Trigger an error and check it's logged (not displayed)
tail -f storage/logs/laravel.log

# Errors should be written to log file, NOT shown to users
```

### 4. Verify Security Headers

```bash
# Check security headers are present
curl -I https://your-domain.com

# Should include:
# X-Frame-Options: SAMEORIGIN
# X-Content-Type-Options: nosniff
# X-XSS-Protection: 1; mode=block
```

### 5. Test Authentication & Authorization

- [ ] Login as each role (Admin, Manager, Staff, Caretaker)
- [ ] Verify role-based access controls work
- [ ] Attempt unauthorized access (should be denied)
- [ ] Test rate limiting (try 5+ failed logins)

### 6. Performance Check

```bash
# Check page load times
time curl https://your-domain.com

# Should be < 2 seconds for most pages
```

---

## Security Headers

The following security headers SHOULD be configured in your web server (see Nginx config above):

| Header | Value | Purpose |
|--------|-------|---------|
| `X-Frame-Options` | `SAMEORIGIN` | Prevent clickjacking attacks |
| `X-Content-Type-Options` | `nosniff` | Prevent MIME-type sniffing |
| `X-XSS-Protection` | `1; mode=block` | Enable browser XSS protection |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Control referrer information |
| `Content-Security-Policy` | See nginx config | Prevent XSS and injection attacks |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | Enforce HTTPS |

**Note:** Task 1.5 (Add security headers middleware) will implement these at the Laravel level as well.

---

## Monitoring & Logging

### Log Files

All logs are stored in `storage/logs/`:

```bash
# Laravel application logs
tail -f storage/logs/laravel.log

# Queue worker logs
tail -f storage/logs/worker.log

# Nginx access logs
tail -f /var/log/nginx/access.log

# Nginx error logs
tail -f /var/log/nginx/error.log
```

### Log Rotation

Configure logrotate for Laravel logs:

Create `/etc/logrotate.d/creams`:

```
/var/www/creams/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### Monitoring Tools (Free)

- **Uptime Monitoring:** [UptimeRobot](https://uptimerobot.com) (free tier)
- **Error Tracking:** Laravel's built-in logging to file
- **Performance:** Laravel Telescope (dev only, NOT for production)
- **Server Monitoring:** `htop`, `iotop`, `nethogs`

### Alerts

Set up email alerts for critical errors:

In `config/logging.php`, configure email channel:

```php
'email' => [
    'driver' => 'monolog',
    'handler' => NativeMailerHandler::class,
    'handler_with' => [
        'to' => 'admin@your-domain.com',
        'subject' => 'CREAMS Production Error',
        'level' => 'critical',
    ],
],
```

---

## Rollback Procedure

If deployment fails:

```bash
# 1. Switch to previous release
cd /var/www/creams
git checkout previous-stable-tag

# 2. Reinstall dependencies
composer install --no-dev --optimize-autoloader

# 3. Rollback database migrations (if needed)
php artisan migrate:rollback --step=1

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
```

---

## Support & Troubleshooting

### Common Issues

#### 1. "Whoops, something went wrong" page appears

**Cause:** APP_DEBUG is false, but error occurred
**Solution:** Check `storage/logs/laravel.log` for actual error

#### 2. 500 Internal Server Error

**Possible causes:**
- File permissions incorrect
- .env file missing or misconfigured
- Database connection failed
- PHP extensions missing

**Debug steps:**
```bash
# Check PHP errors
tail -f /var/log/nginx/error.log

# Check Laravel logs
tail -f storage/logs/laravel.log

# Verify file permissions
ls -la storage/ bootstrap/cache/
```

#### 3. Session/Authentication Issues

**Cause:** SESSION_DOMAIN or SESSION_SECURE_COOKIE misconfigured
**Solution:** Ensure SESSION_SECURE_COOKIE=true only when using HTTPS

#### 4. Assets Not Loading

**Cause:** Incorrect APP_URL or missing asset compilation
**Solution:**
```bash
# Recompile assets
npm run production

# Clear caches
php artisan config:clear
php artisan view:clear
```

---

## Security Incident Response

If you suspect a security breach:

1. **Immediately rotate all credentials:**
   - Database passwords
   - APP_KEY (regenerate)
   - API keys
   - Admin passwords

2. **Check logs for suspicious activity:**
   ```bash
   grep -i "failed\|unauthorized\|error" storage/logs/laravel.log
   ```

3. **Enable maintenance mode:**
   ```bash
   php artisan down --secret="your-secret-bypass-token"
   ```

4. **Investigate and remediate**

5. **Restore service:**
   ```bash
   php artisan up
   ```

---

## Maintenance Schedule

### Daily
- Monitor error logs
- Check disk space usage
- Verify backups completed

### Weekly
- Review security logs
- Update packages: `composer update` (test in staging first)
- Check SSL certificate expiry

### Monthly
- Review user access logs
- Database optimization: `php artisan db:optimize`
- Performance review

---

**For additional support, contact:**
- Technical Lead: [Your Name]
- Emergency Contact: [Emergency Email/Phone]

**Related Documentation:**
- [MASTER_PROGRESS_LOG.md](MASTER_PROGRESS_LOG.md) - Overall project progress
- [SECURITY_BASELINE_SCAN_METHODOLOGY.md](SECURITY_BASELINE_SCAN_METHODOLOGY.md) - Security scanning procedures
- [API_ENDPOINT_SECURITY_INVENTORY.md](API_ENDPOINT_SECURITY_INVENTORY.md) - API security details

---

*Last Updated: 2026-02-06*
*Phase: Phase 1 - Security Hardening*
*Status: Task 1.2 Complete*
