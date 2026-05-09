# Module: Deployment (creams.faisalhanafi.com)

**Status**: IN PROGRESS | **Last verified**: 2026-05-08

---

## Target
`https://creams.faisalhanafi.com`
Server: 54.169.32.54 (AWS Lightsail, Amazon Linux 2023)
Classification: Controlled stakeholder release

## Completed steps (VERIFIED)
- [x] DNS A record: creams.faisalhanafi.com → 54.169.32.54
- [x] Lightsail snapshot created (pre-creams-deployment)
- [x] Nginx + /var/www/creams backups created
- [x] PHP 8.2.29 installed (replaced 8.1 via --allowerasing)
- [x] creams_app database created, creams_user granted
- [x] All 34 migrations ran against creams_app
- [x] Composer install with L12 lock file succeeded
- [x] npm build succeeded
- [x] php artisan about shows correct production config
- [x] storage:link exists

## Blocked steps
- [ ] **UATSeeder fails** — DemoSampleUsersSeeder.php not on server (untracked locally)
- [ ] Nginx config for subdomain not yet created
- [ ] Certbot HTTPS not yet configured

## Server config facts
- PHP-FPM socket: `/run/php-fpm/www.sock`
- Laravel root: `/var/www/creams/public`
- Nginx Portfolio config: `/etc/nginx/conf.d/faisalhanafi.conf` — DO NOT TOUCH
- New CREAMS config will be: `/etc/nginx/conf.d/creams.faisalhanafi.com.conf`

## Required .env on server
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://creams.faisalhanafi.com
DB_DATABASE=creams_app
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
MAIL_MAILER=log
```

## Nginx config (HTTP first, Certbot adds HTTPS)
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name creams.faisalhanafi.com;
    root /var/www/creams/public;
    index index.php;
    charset utf-8;
    client_max_body_size 20M;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
```

## Next steps to complete deployment
1. Commit DemoSampleUsersSeeder.php + UATSeeder.php locally
2. Push to main
3. On server: git pull, composer install (with dev), seed, composer install --no-dev
4. Create Nginx config file
5. nginx -t && systemctl reload nginx
6. certbot --nginx -d creams.faisalhanafi.com
7. Browser verification (all 4 roles)
8. Check Portfolio still returns 200 at each step
