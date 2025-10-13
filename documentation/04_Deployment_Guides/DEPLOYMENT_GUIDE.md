# CREAMS Deployment Guide: Vercel & AWS Migration

## Table of Contents
1. [Vercel Deployment](#vercel-deployment)
2. [Database Configuration](#database-configuration)
3. [Environment Setup](#environment-setup)
4. [Future AWS Migration](#future-aws-migration)
5. [Production Considerations](#production-considerations)

---

## Vercel Deployment

### Overview
While Vercel is primarily designed for static and serverless applications, deploying Laravel requires some workarounds. **Important**: Vercel has limitations for Laravel applications, especially with file uploads, sessions, and database connections.

### Prerequisites
- [Vercel CLI](https://vercel.com/cli) installed globally
- Node.js and npm/yarn
- Composer installed locally
- GitHub/GitLab repository for your CREAMS project

### Step 1: Prepare Laravel for Vercel

#### 1.1 Create vercel.json Configuration
```json
{
  "version": 2,
  "functions": {
    "api/*.php": {
      "runtime": "vercel-php@0.6.0"
    }
  },
  "routes": [
    {
      "src": "/(css|js|images)/(.*)",
      "dest": "public/$1/$2"
    },
    {
      "src": "/build/(.*)",
      "dest": "public/build/$1"
    },
    {
      "src": "/(.*)",
      "dest": "/api/index.php"
    }
  ],
  "env": {
    "APP_ENV": "production",
    "APP_DEBUG": "false",
    "LOG_CHANNEL": "stderr",
    "SESSION_DRIVER": "cookie"
  }
}
```

#### 1.2 Create api/index.php
Create `/api/index.php` file:
```php
<?php

// Forward Vercel requests to Laravel's public/index.php
require_once __DIR__ . '/../public/index.php';
```

#### 1.3 Update Composer Scripts
Add to your `composer.json`:
```json
{
  "scripts": {
    "vercel-build": [
      "@php artisan config:cache",
      "@php artisan route:cache",
      "@php artisan view:cache"
    ]
  }
}
```

### Step 2: Database Configuration for Vercel

#### 2.1 Recommended Database Options

**Option A: PlanetScale (MySQL-compatible)**
```bash
# Install PlanetScale CLI
npm install -g @planetscale/cli

# Create database
pscale database create creams-production

# Get connection string
pscale connect creams-production main --port 3309
```

**Option B: Railway Database**
```bash
# Install Railway CLI  
npm install -g @railway/cli

# Login and create project
railway login
railway init
railway add mysql
```

**Option C: AWS RDS (Recommended for Future Migration)**
```bash
# Create RDS MySQL instance via AWS Console
# Connection string format:
# mysql://username:password@host:port/database
```

#### 2.2 Environment Variables for Database
Set in Vercel dashboard or via CLI:
```bash
vercel env add DB_CONNECTION mysql
vercel env add DB_HOST your-db-host
vercel env add DB_PORT 3306
vercel env add DB_DATABASE creams_production
vercel env add DB_USERNAME your-username  
vercel env add DB_PASSWORD your-password
```

### Step 3: Environment Configuration

#### 3.1 Production Environment Variables
```bash
# Application
vercel env add APP_NAME "CREAMS"
vercel env add APP_ENV production
vercel env add APP_KEY base64:your-generated-key
vercel env add APP_DEBUG false
vercel env add APP_URL https://your-app.vercel.app

# Database (from Step 2.2)

# File Storage (use S3 for production)
vercel env add FILESYSTEM_DRIVER s3
vercel env add AWS_ACCESS_KEY_ID your-access-key
vercel env add AWS_SECRET_ACCESS_KEY your-secret-key  
vercel env add AWS_DEFAULT_REGION us-east-1
vercel env add AWS_BUCKET your-bucket-name

# Mail Configuration
vercel env add MAIL_MAILER smtp
vercel env add MAIL_HOST smtp.gmail.com
vercel env add MAIL_PORT 587
vercel env add MAIL_USERNAME your-email
vercel env add MAIL_PASSWORD your-app-password
vercel env add MAIL_ENCRYPTION tls

# Session Configuration
vercel env add SESSION_DRIVER database
vercel env add SESSION_LIFETIME 120
vercel env add SESSION_SECURE_COOKIE true
```

#### 3.2 Build Configuration
Create `.vercelignore`:
```
/node_modules
/vendor
/storage/logs/*
/storage/app/public/*
/storage/framework/cache/*
/storage/framework/sessions/*
/storage/framework/views/*
/.env.local
/.env.*.local
```

### Step 4: Deploy to Vercel

```bash
# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run production

# Generate application key
php artisan key:generate

# Deploy
vercel --prod

# Run migrations (one-time)
# Note: Vercel doesn't support artisan commands directly
# You'll need to run migrations from a separate script or local environment
```

### Step 5: Post-Deployment Setup

#### 5.1 Database Migration Script
Create `deploy/migrate.php`:
```php
<?php
// Run this locally after deployment to migrate production database
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Set production environment
putenv('APP_ENV=production');

// Run migrations
$kernel->call('migrate', ['--force' => true]);
$kernel->call('db:seed', ['--force' => true]);
```

#### 5.2 File Storage Setup (S3)
```bash
# Create S3 bucket
aws s3 mb s3://creams-production-files

# Set bucket policy for public access to certain folders
aws s3api put-bucket-policy --bucket creams-production-files --policy file://s3-policy.json
```

### Limitations of Vercel for CREAMS
1. **No persistent file system**: User uploads must go to S3/external storage
2. **Function timeout limits**: Long-running processes may fail
3. **Database connections**: Limited concurrent connections
4. **Artisan commands**: Cannot run directly on Vercel
5. **Session storage**: Must use database or external cache

---

## Future AWS Migration

### Phase 1: Infrastructure Setup

#### 1.1 AWS Services Required
- **EC2**: Application servers (t3.medium or t3.large)
- **RDS**: MySQL/PostgreSQL database (db.t3.micro to start)
- **S3**: File storage and static assets
- **CloudFront**: CDN for faster content delivery
- **Route 53**: DNS management
- **Application Load Balancer**: Traffic distribution
- **Auto Scaling Group**: Handle traffic spikes
- **CloudWatch**: Monitoring and logging

#### 1.2 Architecture Diagram
```
Internet → Route 53 → CloudFront → ALB → EC2 Instances
                                      ↓
                                   RDS Database
                                      ↓
                                   S3 Storage
```

### Phase 2: Migration Steps

#### 2.1 Database Migration
```bash
# Export from current database
mysqldump -h current-host -u username -p creams_production > creams_backup.sql

# Import to AWS RDS
mysql -h rds-host -u username -p new_database < creams_backup.sql
```

#### 2.2 File Migration
```bash
# Sync files to S3
aws s3 sync /path/to/current/storage s3://creams-production/storage --delete
```

#### 2.3 Infrastructure as Code (Terraform)
Create `infrastructure/main.tf`:
```hcl
provider "aws" {
  region = "us-east-1"
}

# VPC and networking
resource "aws_vpc" "main" {
  cidr_block           = "10.0.0.0/16"
  enable_dns_hostnames = true
  enable_dns_support   = true

  tags = {
    Name = "creams-vpc"
  }
}

# RDS Database
resource "aws_db_instance" "main" {
  identifier     = "creams-production"
  engine         = "mysql"
  engine_version = "8.0"
  instance_class = "db.t3.micro"
  allocated_storage = 20
  
  db_name  = "creams_production"
  username = "creams_admin"
  password = var.db_password
  
  vpc_security_group_ids = [aws_security_group.rds.id]
  db_subnet_group_name   = aws_db_subnet_group.main.name
  
  backup_retention_period = 7
  backup_window          = "03:00-04:00"
  maintenance_window     = "sun:04:00-sun:05:00"
  
  skip_final_snapshot = false
  final_snapshot_identifier = "creams-final-snapshot"
  
  tags = {
    Name = "creams-production-db"
  }
}

# EC2 Launch Template
resource "aws_launch_template" "main" {
  name_prefix   = "creams-"
  image_id      = "ami-0c02fb55956c7d316" # Amazon Linux 2
  instance_type = "t3.medium"
  
  user_data = base64encode(templatefile("${path.module}/userdata.sh", {
    db_host = aws_db_instance.main.endpoint
  }))
  
  tag_specifications {
    resource_type = "instance"
    tags = {
      Name = "creams-web-server"
    }
  }
}

# Auto Scaling Group
resource "aws_autoscaling_group" "main" {
  name                = "creams-asg"
  vpc_zone_identifier = aws_subnet.private[*].id
  target_group_arns   = [aws_lb_target_group.main.arn]
  health_check_type   = "ELB"
  
  min_size         = 1
  max_size         = 3
  desired_capacity = 2
  
  launch_template {
    id      = aws_launch_template.main.id
    version = "$Latest"
  }
}
```

#### 2.4 User Data Script (userdata.sh)
```bash
#!/bin/bash
yum update -y
yum install -y php8.1 php8.1-cli php8.1-mysql php8.1-xml php8.1-mbstring
yum install -y nginx composer

# Install Laravel application
cd /var/www/html
git clone https://github.com/your-username/creams.git
cd creams
composer install --optimize-autoloader --no-dev

# Configure environment
cp .env.production .env
sed -i 's/DB_HOST=.*/DB_HOST=${db_host}/' .env

# Set permissions
chown -R nginx:nginx /var/www/html
chmod -R 755 storage bootstrap/cache

# Start services
systemctl enable nginx php-fpm
systemctl start nginx php-fpm
```

### Phase 3: Deployment Pipeline

#### 3.1 GitHub Actions Workflow
Create `.github/workflows/deploy.yml`:
```yaml
name: Deploy to AWS

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.1
        
    - name: Install dependencies
      run: |
        composer install --optimize-autoloader --no-dev
        npm install && npm run production
        
    - name: Run tests
      run: php artisan test
      
    - name: Deploy to EC2
      uses: easingthemes/ssh-deploy@v2.1.5
      env:
        SSH_PRIVATE_KEY: ${{ secrets.AWS_SSH_KEY }}
        REMOTE_HOST: ${{ secrets.AWS_HOST }}
        REMOTE_USER: ec2-user
        SOURCE: "./"
        TARGET: "/var/www/html/creams"
        
    - name: Run migrations
      run: |
        ssh ec2-user@${{ secrets.AWS_HOST }} 'cd /var/www/html/creams && php artisan migrate --force'
```

### Phase 4: Monitoring and Scaling

#### 4.1 CloudWatch Monitoring
```bash
# Install CloudWatch agent
sudo yum install amazon-cloudwatch-agent

# Configure metrics
sudo /opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-config-wizard
```

#### 4.2 Application Performance Monitoring
Add to `config/app.php`:
```php
'providers' => [
    // Other providers...
    App\Providers\MonitoringServiceProvider::class,
],
```

Create monitoring service:
```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class MonitoringServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Track response times
        $this->app['events']->listen('Illuminate\Foundation\Http\Events\RequestHandled', function ($event) {
            $duration = microtime(true) - LARAVEL_START;
            
            if ($duration > 2.0) { // Log slow requests
                Log::warning('Slow request detected', [
                    'url' => $event->request->url(),
                    'duration' => $duration
                ]);
            }
        });
    }
}
```

## Production Considerations

### Security Checklist
- [ ] SSL/TLS certificates configured
- [ ] Database encrypted at rest
- [ ] Secrets stored in AWS Secrets Manager
- [ ] VPC with private subnets
- [ ] Security groups with minimal required ports
- [ ] WAF enabled on CloudFront
- [ ] Regular security updates automated

### Performance Optimization
- [ ] Laravel config/route/view caching enabled
- [ ] OPcache configured for PHP
- [ ] Database query optimization
- [ ] CDN for static assets
- [ ] Redis for session/cache storage
- [ ] Database read replicas for scaling

### Backup Strategy
- [ ] Automated RDS backups (7-day retention)
- [ ] S3 versioning enabled
- [ ] Application code in version control
- [ ] Database dump automation via Lambda

### Cost Optimization
- [ ] Use Reserved Instances for predictable workloads
- [ ] Implement auto-scaling based on CPU/memory
- [ ] Use S3 Intelligent-Tiering
- [ ] Monitor costs with AWS Cost Explorer
- [ ] Set up billing alerts

---

## Migration Timeline

| Phase | Duration | Key Activities |
|-------|----------|----------------|
| **Phase 1: Vercel** | 1-2 weeks | Deploy to Vercel with external database |
| **Phase 2: AWS Prep** | 2-3 weeks | Setup AWS infrastructure, testing |
| **Phase 3: Migration** | 1 week | Data migration, DNS cutover |
| **Phase 4: Optimization** | Ongoing | Performance tuning, monitoring |

## Support and Troubleshooting

### Common Issues
1. **Database Connection Timeouts**: Increase connection pool size
2. **File Upload Issues**: Ensure S3 permissions are correct
3. **Session Issues**: Use database sessions for multi-server setup
4. **Memory Limits**: Increase PHP memory_limit for large operations

### Getting Help
- AWS Support (if subscribed)
- Laravel Documentation: https://laravel.com/docs
- AWS Documentation: https://docs.aws.amazon.com/
- CREAMS GitHub Issues: [Your repository]/issues

---
**Note**: This guide assumes familiarity with basic AWS concepts. Consider hiring a DevOps consultant for complex deployments or if you're new to AWS.