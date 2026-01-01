# faisalhanafi.com Domain Setup Guide

## Complete Setup for CREAMS Demo Instances

**Domain:** faisalhanafi.com
**Hosting:** AWS EC2 (Free Tier)
**DNS Provider:** Namecheap

---

## URL Structure

```
faisalhanafi.com/                           → Portfolio (placeholder)
faisalhanafi.com/creams/demo/               → CREAMS default demo
faisalhanafi.com/creams/demo/login          → Demo login page
faisalhanafi.com/creams/demo/dashboard      → Demo dashboard
faisalhanafi.com/creams/demo1/              → Alternate demo instance
faisalhanafi.com/creams/staging/            → Staging environment
```

---

## Step 1: Namecheap DNS Configuration

### 1.1 Login to Namecheap

```
1. Go to namecheap.com and login
2. Click "Domain List" in dashboard
3. Find faisalhanafi.com and click "Manage"
4. Click "Advanced DNS" tab
```

### 1.2 Add DNS Records

Delete any existing A records, then add:

| Type | Host | Value | TTL |
|------|------|-------|-----|
| A Record | @ | [Your EC2 Elastic IP] | Automatic |
| A Record | www | [Your EC2 Elastic IP] | Automatic |

**Example:**
```
Type: A Record
Host: @
Value: 54.169.123.45
TTL: Automatic
```

### 1.3 Verify DNS Propagation

```bash
# Check from terminal
nslookup faisalhanafi.com

# Or use online checker
# https://dnschecker.org/#A/faisalhanafi.com
```

DNS propagation takes 5-30 minutes (up to 48 hours in rare cases).

---

## Step 2: EC2 Server Setup

### 2.1 SSH into EC2

```bash
ssh -i creams-key.pem ec2-user@[Your-Elastic-IP]
```

### 2.2 Create Directory Structure

```bash
# Portfolio directory
sudo mkdir -p /var/www/portfolio
sudo chown ec2-user:ec2-user /var/www/portfolio

# CREAMS should already be at /var/www/creams
ls -la /var/www/creams
```

### 2.3 Create Portfolio Placeholder

```bash
cat > /var/www/portfolio/index.html << 'EOF'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faisal Hanafi - Portfolio</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { text-align: center; padding: 2rem; }
        h1 { font-size: 3rem; margin-bottom: 1rem; }
        p { font-size: 1.2rem; color: #a0a0a0; margin-bottom: 2rem; }
        .status {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
        }
        .demo-link { margin-top: 2rem; }
        .demo-link a {
            color: #4fc3f7;
            text-decoration: none;
            padding: 0.8rem 2rem;
            border: 2px solid #4fc3f7;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .demo-link a:hover {
            background: #4fc3f7;
            color: #1a1a2e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Faisal Hanafi</h1>
        <p>Portfolio coming soon...</p>
        <span class="status">🚧 Under Construction</span>
        <div class="demo-link">
            <a href="/creams/demo/">View CREAMS Demo →</a>
        </div>
    </div>
</body>
</html>
EOF
```

---

## Step 3: Nginx Configuration

### 3.1 Copy Nginx Config

```bash
# Copy from repository
sudo cp /var/www/creams/docker/nginx/faisalhanafi.conf /etc/nginx/conf.d/

# Or create manually
sudo nano /etc/nginx/conf.d/faisalhanafi.conf
```

### 3.2 Nginx Configuration Content

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name faisalhanafi.com www.faisalhanafi.com;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;

    # CREAMS static assets
    location ~ ^/creams/[a-zA-Z0-9_-]+/(css|js|images|build|storage|fonts|vendor)/(.*)$ {
        alias /var/www/creams/public/$1/$2;
        expires 30d;
        access_log off;
    }

    # CREAMS application
    location ~ ^/creams/([a-zA-Z0-9_-]+)(/.*)?$ {
        set $demo_id $1;
        root /var/www/creams/public;
        try_files /nonexistent @creams_php;
    }

    # PHP handler for CREAMS
    location @creams_php {
        root /var/www/creams/public;
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME /var/www/creams/public/index.php;
        fastcgi_param REQUEST_URI $request_uri;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Portfolio (root)
    location / {
        root /var/www/portfolio;
        index index.html;
        try_files $uri $uri/ =404;
    }

    # Health check
    location /health {
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }

    # Deny hidden files
    location ~ /\. {
        deny all;
    }
}
```

### 3.3 Test and Reload Nginx

```bash
# Remove default config if exists
sudo rm -f /etc/nginx/conf.d/default.conf

# Test configuration
sudo nginx -t

# If OK, reload
sudo systemctl reload nginx
```

---

## Step 4: Laravel Configuration

### 4.1 Update Composer Autoload

```bash
cd /var/www/creams
composer dump-autoload
```

### 4.2 Update .env

```bash
nano /var/www/creams/.env
```

Update these values:
```env
APP_URL=https://faisalhanafi.com
APP_ENV=production
APP_DEBUG=false
```

### 4.3 Clear Caches

```bash
cd /var/www/creams
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4.4 Set Permissions

```bash
sudo chown -R nginx:nginx /var/www/creams
sudo chmod -R 755 /var/www/creams
sudo chmod -R 775 /var/www/creams/storage
sudo chmod -R 775 /var/www/creams/bootstrap/cache
```

---

## Step 5: SSL Certificate (Let's Encrypt)

### 5.1 Install Certbot

```bash
# Amazon Linux 2023
sudo dnf install -y certbot python3-certbot-nginx
```

### 5.2 Obtain Certificate

```bash
sudo certbot --nginx -d faisalhanafi.com -d www.faisalhanafi.com
```

Follow the prompts:
- Enter email address
- Agree to terms
- Choose whether to redirect HTTP to HTTPS (recommended: Yes)

### 5.3 Test Auto-Renewal

```bash
sudo certbot renew --dry-run
```

### 5.4 Set Up Auto-Renewal Cron

```bash
# Certbot usually sets this up automatically, but verify:
sudo crontab -l

# Should show something like:
# 0 0,12 * * * /usr/bin/certbot renew --quiet
```

---

## Step 6: Verification

### 6.1 Test URLs

```bash
# Portfolio
curl -I https://faisalhanafi.com
# Expected: HTTP/2 200

# CREAMS demo
curl -I https://faisalhanafi.com/creams/demo/
# Expected: HTTP/2 200 (or 302 redirect to login)

# Health check
curl https://faisalhanafi.com/health
# Expected: healthy
```

### 6.2 Browser Testing

Open in browser and verify:

| URL | Expected Result |
|-----|-----------------|
| `https://faisalhanafi.com` | Portfolio placeholder |
| `https://faisalhanafi.com/creams/demo/` | CREAMS homepage or login |
| `https://faisalhanafi.com/creams/demo/login` | Login page |
| `https://faisalhanafi.com/creams/demo1/` | Separate demo instance |

### 6.3 Login Test

```
1. Go to: https://faisalhanafi.com/creams/demo/login
2. Enter: admin@creams.test / Admin123!
3. Should redirect to: /creams/demo/dashboard
```

---

## Troubleshooting

### Issue: 404 Not Found

```bash
# Check Nginx error log
sudo tail -f /var/log/nginx/error.log

# Verify CREAMS files exist
ls -la /var/www/creams/public/index.php
```

### Issue: 500 Internal Server Error

```bash
# Check Laravel logs
tail -f /var/www/creams/storage/logs/laravel.log

# Check PHP-FPM status
sudo systemctl status php-fpm
```

### Issue: Permission Denied

```bash
# Fix permissions
sudo chown -R nginx:nginx /var/www/creams
sudo chmod -R 775 /var/www/creams/storage
```

### Issue: Static Assets Not Loading

```bash
# Rebuild assets
cd /var/www/creams
npm run build

# Clear view cache
php artisan view:clear
```

---

## Demo Instance Management

### Creating New Demo Instances

Demo instances are dynamically handled by the URL pattern. Simply use a new demo_id in the URL:

- `/creams/client1/` - Creates isolated session for client1
- `/creams/presentation/` - Creates isolated session for presentation

### Session Isolation

Each demo_id gets its own session cookie:
- `creams_demo_session`
- `creams_demo1_session`
- `creams_staging_session`

This means users logged into `/creams/demo/` won't be logged into `/creams/demo1/`.

---

## Security Considerations

1. **Demo instances share the same database** - All demo instances read/write to the same database. Consider creating read-only demo modes if needed.

2. **Session isolation is cookie-based** - Different demo instances use different session cookies, but the data is not isolated.

3. **Rate limiting** - Consider adding rate limiting for demo instances to prevent abuse.

4. **Restrict demo IDs** - In production, you may want to restrict which demo IDs are valid by modifying `DemoInstanceMiddleware.php`.

---

**Last Updated:** December 2025
**Author:** CREAMS Development Team
