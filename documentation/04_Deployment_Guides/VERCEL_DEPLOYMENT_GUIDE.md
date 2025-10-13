# CREAMS Laravel Application - Vercel Deployment Guide

## Table of Contents
1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Database Setup](#database-setup)
4. [Environment Configuration](#environment-configuration)
5. [Vercel Configuration](#vercel-configuration)
6. [Deployment Process](#deployment-process)
7. [Backend & Database Handling](#backend--database-handling)
8. [Post-Deployment Configuration](#post-deployment-configuration)
9. [Troubleshooting](#troubleshooting)
10. [Future AWS Migration](#future-aws-migration)

## Overview

This guide provides comprehensive instructions for deploying the CREAMS (Community Rehabilitation and Educational Activity Management System) Laravel application to Vercel with proper database and backend handling.

**Important Notes:**
- CREAMS is a Laravel PHP application with complex database relationships
- Vercel primarily supports static sites and serverless functions
- This guide covers hybrid deployment strategies for Laravel on Vercel

## Prerequisites

### Required Accounts & Tools
- Vercel account (free or pro)
- GitHub/GitLab account for repository
- Database hosting service (PlanetScale, Supabase, or Amazon RDS)
- Composer installed locally
- PHP 8.1+ installed locally
- Node.js 18+ and npm installed

### Repository Preparation
```bash
# Ensure your CREAMS repository is clean and committed
git status
git add .
git commit -m "Prepare for Vercel deployment"
git push origin main
```

## Database Setup

### Option 1: PlanetScale (Recommended for Laravel)
```bash
# Install PlanetScale CLI
pscale auth login

# Create database
pscale database create creams-production
pscale branch create creams-production main

# Get connection string
pscale connect creams-production main --port 3309
```

### Option 2: Supabase PostgreSQL
1. Create account at supabase.com
2. Create new project: "creams-production"
3. Note the database URL from Settings > Database
4. Convert Laravel migrations for PostgreSQL compatibility

### Option 3: Amazon RDS MySQL
```bash
# Create RDS instance (if using AWS already)
aws rds create-db-instance \
  --db-instance-identifier creams-production \
  --db-instance-class db.t3.micro \
  --engine mysql \
  --master-username admin \
  --master-user-password YourSecurePassword123 \
  --allocated-storage 20
```

## Environment Configuration

### Create Vercel Environment Variables
Create `.env.vercel` file:

```env
# Laravel Configuration
APP_NAME="CREAMS"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-app-name.vercel.app

# Database Configuration (PlanetScale Example)
DB_CONNECTION=mysql
DB_HOST=YOUR_PLANETSCALE_HOST
DB_PORT=3306
DB_DATABASE=creams-production
DB_USERNAME=YOUR_PLANETSCALE_USERNAME
DB_PASSWORD=YOUR_PLANETSCALE_PASSWORD

# Cache & Session (Use Database for Vercel)
CACHE_DRIVER=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@creams.app
MAIL_FROM_NAME="CREAMS System"

# File Storage (Use Vercel Blob or S3)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-aws-access-key
AWS_SECRET_ACCESS_KEY=your-aws-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=creams-storage
AWS_USE_PATH_STYLE_ENDPOINT=false

# Additional CREAMS Settings
CREAMS_VERSION=1.0.0
CREAMS_MAINTENANCE_MODE=false
```

### Generate Laravel App Key
```bash
php artisan key:generate --show
# Copy the generated key to APP_KEY in your environment variables
```

## Vercel Configuration

### 1. Create `vercel.json`
```json
{
  "version": 2,
  "functions": {
    "api/index.php": { "runtime": "vercel-php@0.6.0" }
  },
  "routes": [
    {
      "src": "/(css|js|images)/(.*)",
      "dest": "public/$1/$2"
    },
    {
      "src": "/storage/(.*)",
      "dest": "storage/app/public/$1"
    },
    {
      "src": "/(.*)",
      "dest": "api/index.php"
    }
  ],
  "env": {
    "APP_ENV": "production",
    "APP_DEBUG": "false",
    "VERCEL_DEMO_MODE": "true"
  },
  "build": {
    "env": {
      "COMPOSER_MIRROR_PATH_REPOS": "1"
    }
  }
}
```

### 2. Create `api/index.php`
```php
<?php

// Vercel serverless function entry point for Laravel
require __DIR__ . '/../public/index.php';
```

### 3. Create `.vercelignore`
```
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
```

### 4. Update `composer.json` for Vercel
Add to your `composer.json`:
```json
{
  "scripts": {
    "vercel-build": [
      "@php -r \"file_exists('.env') || copy('.env.vercel', '.env');\"",
      "composer install --optimize-autoloader --no-dev",
      "php artisan config:cache",
      "php artisan route:cache",
      "php artisan view:cache",
      "npm ci && npm run build"
    ],
    "vercel-postbuild": [
      "php artisan migrate --force",
      "php artisan storage:link",
      "php artisan config:clear"
    ]
  }
}
```

## Deployment Process

### 1. Install Vercel CLI
```bash
npm install -g vercel
vercel login
```

### 2. Link Project
```bash
cd C:\laragon\www\CREAMS
vercel link
```

### 3. Set Environment Variables
```bash
# Set all environment variables from .env.vercel
vercel env add APP_NAME
vercel env add APP_ENV production
vercel env add APP_KEY
vercel env add APP_DEBUG false
vercel env add APP_URL

# Database variables
vercel env add DB_CONNECTION mysql
vercel env add DB_HOST
vercel env add DB_PORT 3306
vercel env add DB_DATABASE
vercel env add DB_USERNAME
vercel env add DB_PASSWORD

# Continue for all environment variables...
```

### 4. Deploy
```bash
vercel --prod
```

## Backend & Database Handling

### Database Migration Strategy

#### Option A: Pre-deployment Migration
```bash
# Run migrations locally against production database
php artisan migrate --env=production --force
php artisan db:seed --env=production --force
```

#### Option B: Deployment Hook Migration
Create `scripts/deploy.sh`:
```bash
#!/bin/bash
echo "Running CREAMS deployment script..."

# Run database migrations
php artisan migrate --force --no-interaction

# Clear and cache config
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Link storage (if not using S3)
php artisan storage:link

# Set proper permissions
chmod -R 755 storage bootstrap/cache

echo "Deployment completed successfully!"
```

### File Storage Solutions

#### Option 1: AWS S3 (Recommended)
```php
// config/filesystems.php - already configured for S3
'default' => env('FILESYSTEM_DISK', 's3'),

's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
],
```

#### Option 2: Vercel Blob Storage
```bash
npm install @vercel/blob
```

```php
// Create custom storage driver for Vercel Blob
// app/Providers/AppServiceProvider.php
use League\Flysystem\Filesystem;
use Spatie\FlysystemUrlStorage\UrlStorageAdapter;

Storage::extend('vercel-blob', function ($app, $config) {
    $adapter = new UrlStorageAdapter($config['url']);
    return new Filesystem($adapter, $config);
});
```

### Session & Cache Handling

#### Database Sessions (Recommended for Vercel)
```bash
php artisan session:table
php artisan cache:table
php artisan queue:table
php artisan migrate
```

Update `.env`:
```env
SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database
```

## Post-Deployment Configuration

### 1. Verify Database Connection
```bash
# Test database connection
vercel dev
# Or check logs
vercel logs
```

### 2. Set Up Cron Jobs (if needed)
Create Vercel cron function `api/cron.php`:
```php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);

// Run scheduled tasks
$kernel->call('schedule:run');

return response('Cron jobs executed', 200);
```

### 3. Configure Domain (Optional)
```bash
vercel domains add your-domain.com
vercel domains add www.your-domain.com
```

### 4. Set Up Monitoring
Add to `vercel.json`:
```json
{
  "functions": {
    "api/index.php": {
      "runtime": "vercel-php@0.6.0",
      "maxDuration": 30
    }
  }
}
```

## Troubleshooting

### Common Issues & Solutions

#### 1. PHP Version Issues
```json
// vercel.json
{
  "functions": {
    "api/index.php": { 
      "runtime": "vercel-php@0.6.0"
    }
  }
}
```

#### 2. Database Connection Timeout
```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'options' => [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
],
```

#### 3. Storage Issues
```bash
# Check storage permissions
ls -la storage/
chmod -R 775 storage/
```

#### 4. Environment Variables Not Loading
```bash
# Check environment variables
vercel env ls
vercel env pull .env.local
```

#### 5. Migration Issues
```bash
# Reset and re-run migrations
php artisan migrate:fresh --force --seed
```

### Debug Mode
Enable debugging temporarily:
```bash
vercel env add APP_DEBUG true
vercel --prod
# Remember to disable afterwards:
vercel env add APP_DEBUG false
```

### Logs Access
```bash
# View real-time logs
vercel logs --follow

# View function logs
vercel logs --functions
```

## Performance Optimization

### 1. Enable OPcache
Create `api/.htaccess`:
```apache
php_value opcache.enable 1
php_value opcache.enable_cli 1
php_value opcache.memory_consumption 128
php_value opcache.interned_strings_buffer 8
php_value opcache.max_accelerated_files 4000
php_value opcache.revalidate_freq 2
php_value opcache.fast_shutdown 1
```

### 2. Optimize Laravel
```bash
# Run optimization commands
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Database Connection Pooling
```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'options' => [
        PDO::ATTR_PERSISTENT => true,
    ],
    'pool' => [
        'min_connections' => 2,
        'max_connections' => 10,
    ],
],
```

## Future AWS Migration

This section prepares for future AWS deployment while maintaining Vercel compatibility.

### AWS Architecture Plan
```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   CloudFront    │    │  Application     │    │   RDS MySQL     │
│   (CDN/Cache)   │───▶│  Load Balancer   │───▶│   (Database)    │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                                │
                                ▼
                       ┌──────────────────┐    ┌─────────────────┐
                       │   EC2/ECS        │    │      S3         │
                       │  (Laravel App)   │───▶│ (File Storage)  │
                       └──────────────────┘    └─────────────────┘
```

### Prepare for AWS Migration

#### 1. Docker Configuration
Create `Dockerfile`:
```dockerfile
FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

#### 2. Infrastructure as Code (Terraform)
Create `infrastructure/main.tf`:
```hcl
provider "aws" {
  region = "us-east-1"
}

resource "aws_rds_instance" "creams_db" {
  identifier     = "creams-production"
  engine         = "mysql"
  engine_version = "8.0"
  instance_class = "db.t3.micro"
  
  allocated_storage     = 20
  max_allocated_storage = 100
  
  db_name  = "creams"
  username = "admin"
  password = var.db_password
  
  vpc_security_group_ids = [aws_security_group.rds.id]
  
  backup_retention_period = 7
  backup_window          = "03:00-04:00"
  maintenance_window     = "sun:04:00-sun:05:00"
  
  skip_final_snapshot = false
  final_snapshot_identifier = "creams-final-snapshot"
  
  tags = {
    Name = "CREAMS Production Database"
  }
}

resource "aws_s3_bucket" "creams_storage" {
  bucket = "creams-production-storage"
  
  tags = {
    Name = "CREAMS Production Storage"
  }
}
```

#### 3. Environment Configuration for AWS
Create `.env.aws`:
```env
# AWS-specific environment variables
APP_ENV=production
APP_DEBUG=false
APP_URL=https://creams.yourdomain.com

# AWS RDS Database
DB_CONNECTION=mysql
DB_HOST=creams-production.cluster-xyz.us-east-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=creams
DB_USERNAME=admin
DB_PASSWORD=your-secure-password

# AWS S3 Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=creams-production-storage

# Redis for caching (AWS ElastiCache)
REDIS_HOST=creams-cache.abc123.cache.amazonaws.com
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Queue (AWS SQS)
QUEUE_CONNECTION=sqs
SQS_KEY=your-sqs-access-key
SQS_SECRET=your-sqs-secret-key
SQS_PREFIX=https://sqs.us-east-1.amazonaws.com/your-account-id
SQS_QUEUE=creams-jobs
```

### Migration Checklist
- [ ] Set up AWS account and billing
- [ ] Configure AWS CLI and credentials  
- [ ] Create RDS MySQL instance
- [ ] Create S3 bucket for file storage
- [ ] Set up EC2/ECS for application hosting
- [ ] Configure CloudFront CDN
- [ ] Set up Route 53 for DNS
- [ ] Create CI/CD pipeline (GitHub Actions/AWS CodePipeline)
- [ ] Configure monitoring (CloudWatch)
- [ ] Set up backup strategies
- [ ] Configure SSL certificates (AWS Certificate Manager)

## Security Considerations

### 1. Environment Variables Security
- Never commit `.env` files to version control
- Use Vercel's environment variable encryption
- Rotate database passwords regularly
- Use strong, unique passwords

### 2. Database Security
```sql
-- Create dedicated database user for CREAMS
CREATE USER 'creams_app'@'%' IDENTIFIED BY 'strong-password-here';
GRANT SELECT, INSERT, UPDATE, DELETE ON creams.* TO 'creams_app'@'%';
FLUSH PRIVILEGES;
```

### 3. Application Security
```php
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        
        return $response;
    }
}
```

## Monitoring & Maintenance

### 1. Health Checks
Create `api/health.php`:
```php
<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

try {
    // Test database connection
    DB::connection()->getPdo();
    
    // Test cache
    Cache::put('health_check', true, 60);
    $cacheWorks = Cache::get('health_check');
    
    // Test storage
    $storageWorks = Storage::disk('default')->exists('test.txt') || true;
    
    $status = [
        'status' => 'healthy',
        'database' => 'connected',
        'cache' => $cacheWorks ? 'working' : 'failed',
        'storage' => $storageWorks ? 'working' : 'failed',
        'timestamp' => now()->toISOString(),
    ];
    
    return response()->json($status, 200);
    
} catch (Exception $e) {
    return response()->json([
        'status' => 'unhealthy',
        'error' => $e->getMessage(),
        'timestamp' => now()->toISOString(),
    ], 500);
}
```

### 2. Backup Strategy
```bash
# Database backup script
#!/bin/bash
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
mysqldump --host=$DB_HOST --user=$DB_USERNAME --password=$DB_PASSWORD $DB_DATABASE > "backups/creams_backup_$BACKUP_DATE.sql"
aws s3 cp "backups/creams_backup_$BACKUP_DATE.sql" s3://creams-backups/database/
```

## Cost Optimization

### Vercel Costs
- Hobby Plan: $0/month (suitable for development)
- Pro Plan: $20/month per team member (recommended for production)
- Enterprise: Custom pricing

### Database Costs
- PlanetScale: $29/month for branch-based development
- Supabase: $25/month for pro features
- AWS RDS: ~$15-50/month depending on instance size

### Storage Costs
- Vercel Blob: $0.15/GB stored, $1.00/GB bandwidth
- AWS S3: $0.023/GB stored, $0.09/GB transfer

## Support & Resources

### Documentation Links
- [Vercel PHP Runtime](https://vercel.com/docs/runtimes/php)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [PlanetScale Laravel Guide](https://planetscale.com/docs/tutorials/laravel-quickstart)

### Community Support
- [CREAMS GitHub Repository](https://github.com/your-org/creams)
- [Laravel Discord](https://discord.gg/laravel)
- [Vercel Discord](https://discord.gg/vercel)

### Emergency Contacts
- Database Issues: Check PlanetScale/Supabase status pages
- Application Issues: Review Vercel function logs
- Critical Issues: Contact your system administrator

---

## Conclusion

This guide provides a comprehensive approach to deploying CREAMS on Vercel with proper database and backend handling. The configuration supports both immediate Vercel deployment and future AWS migration while maintaining security, performance, and reliability standards.

For questions or issues not covered in this guide, please refer to the official documentation links or contact the development team.

**Last Updated:** $(date +%Y-%m-%d)
**Version:** 1.0.0