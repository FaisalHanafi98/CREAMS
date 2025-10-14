# CREAMS Laravel Application - AWS Deployment & Migration Guide

## Table of Contents
1. [Overview](#overview)
2. [AWS Architecture Design](#aws-architecture-design)
3. [Prerequisites](#prerequisites)
4. [Database Migration Strategy](#database-migration-strategy)
5. [Infrastructure Setup](#infrastructure-setup)
6. [Application Deployment](#application-deployment)
7. [Migration from Vercel to AWS](#migration-from-vercel-to-aws)
8. [Monitoring and Logging](#monitoring-and-logging)
9. [Security Implementation](#security-implementation)
10. [Cost Optimization](#cost-optimization)
11. [Disaster Recovery](#disaster-recovery)
12. [Maintenance and Updates](#maintenance-and-updates)

## Overview

This guide provides a comprehensive roadmap for migrating the CREAMS (Community Rehabilitation and Educational Activity Management System) Laravel application from Vercel to AWS, implementing enterprise-grade infrastructure with high availability, scalability, and security.

### Migration Benefits
- **Better Performance**: Dedicated resources vs serverless limitations
- **Cost Efficiency**: Predictable pricing for sustained workloads  
- **Enhanced Security**: VPC, WAF, and enterprise security features
- **Scalability**: Auto-scaling and load balancing capabilities
- **Database Control**: Managed RDS with backup and monitoring
- **File Storage**: S3 with CDN distribution via CloudFront

## AWS Architecture Design

### High-Level Architecture
```
Internet Gateway
       │
   ┌───▼───┐
   │  WAF  │ (Web Application Firewall)
   └───┬───┘
       │
 ┌─────▼─────┐
 │CloudFront │ (CDN + Static Assets)
 │    CDN    │
 └─────┬─────┘
       │
┌──────▼──────┐
│Load Balancer│ (Application Load Balancer)
│     ALB     │
└──────┬──────┘
       │
   ┌───▼────┐     ┌────────────┐
   │   ECS  │────▶│   RDS      │
   │Fargate │     │   MySQL    │
   │Cluster │     │Multi-AZ    │
   └───┬────┘     └────────────┘
       │
   ┌───▼────┐     ┌────────────┐
   │   S3   │     │ElastiCache │
   │Storage │     │   Redis    │
   └────────┘     └────────────┘
```

### Detailed Component Architecture

#### Compute Layer - ECS Fargate
```yaml
Service: AWS ECS (Elastic Container Service)
Platform: Fargate (Serverless containers)
Benefits:
  - No server management
  - Automatic scaling
  - Pay per use
  - High availability across AZs
```

#### Database Layer - RDS MySQL
```yaml
Service: Amazon RDS for MySQL
Configuration: Multi-AZ deployment
Benefits:
  - Automated backups
  - Point-in-time recovery
  - High availability
  - Managed maintenance
```

#### Caching Layer - ElastiCache Redis
```yaml
Service: Amazon ElastiCache for Redis
Configuration: Cluster mode enabled
Benefits:
  - Session storage
  - Application caching
  - Real-time analytics
  - High performance
```

#### Storage Layer - S3 + CloudFront
```yaml
S3 Services:
  - Static assets storage
  - File uploads storage
  - Application backups
  - Logs storage

CloudFront:
  - Global CDN
  - SSL termination
  - Static asset delivery
  - API acceleration
```

## Prerequisites

### AWS Account Setup
```bash
# Install AWS CLI
curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
unzip awscliv2.zip
sudo ./aws/install

# Configure AWS CLI
aws configure
# Enter: Access Key ID, Secret Access Key, Region (us-east-1), Output format (json)
```

### Required IAM Permissions
```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "ec2:*",
        "ecs:*",
        "rds:*",
        "s3:*",
        "cloudfront:*",
        "elasticache:*",
        "elbv2:*",
        "logs:*",
        "iam:PassRole",
        "ecr:*"
      ],
      "Resource": "*"
    }
  ]
}
```

### Development Tools
```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Install Terraform
wget https://releases.hashicorp.com/terraform/1.6.0/terraform_1.6.0_linux_amd64.zip
unzip terraform_1.6.0_linux_amd64.zip
sudo mv terraform /usr/local/bin/

# Install kubectl
curl -LO "https://dl.k8s.io/release/$(curl -L -s https://dl.k8s.io/release/stable.txt)/bin/linux/amd64/kubectl"
sudo install -o root -g root -m 0755 kubectl /usr/local/bin/kubectl
```

## Database Migration Strategy

### Phase 1: Database Assessment
```bash
# Analyze current database size and structure
mysqldump --host=YOUR_CURRENT_DB_HOST --user=USERNAME --password=PASSWORD \
  --single-transaction --routines --triggers --no-data CREAMS > schema.sql

# Get database size
mysql -h YOUR_CURRENT_DB_HOST -u USERNAME -p -e "
SELECT 
    table_schema 'Database Name',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) 'Database Size (MB)'
FROM information_schema.tables 
WHERE table_schema='CREAMS';"
```

### Phase 2: RDS Setup
```bash
# Create RDS instance using AWS CLI
aws rds create-db-instance \
  --db-instance-identifier creams-production \
  --db-instance-class db.t3.medium \
  --engine mysql \
  --engine-version 8.0.35 \
  --allocated-storage 100 \
  --max-allocated-storage 1000 \
  --storage-type gp2 \
  --storage-encrypted \
  --master-username admin \
  --master-user-password 'YourSecurePassword123!' \
  --vpc-security-group-ids sg-xxxxxxxxx \
  --db-subnet-group-name creams-db-subnet-group \
  --backup-retention-period 7 \
  --preferred-backup-window '03:00-04:00' \
  --preferred-maintenance-window 'sun:04:00-sun:05:00' \
  --multi-az \
  --publicly-accessible \
  --enable-performance-insights \
  --performance-insights-retention-period 7 \
  --deletion-protection \
  --tags Key=Application,Value=CREAMS Key=Environment,Value=Production
```

### Phase 3: Data Migration
```bash
# Create migration script
#!/bin/bash
# migrate-database.sh

SOURCE_HOST="your-current-db-host"
SOURCE_USER="username"
SOURCE_PASS="password"
SOURCE_DB="creams"

TARGET_HOST="creams-production.cluster-xyz.us-east-1.rds.amazonaws.com"
TARGET_USER="admin"
TARGET_PASS="YourSecurePassword123!"
TARGET_DB="creams"

echo "Starting database migration..."

# Step 1: Export schema
echo "Exporting schema..."
mysqldump --host=$SOURCE_HOST --user=$SOURCE_USER --password=$SOURCE_PASS \
  --single-transaction --routines --triggers --no-data $SOURCE_DB > schema.sql

# Step 2: Import schema to RDS
echo "Importing schema to RDS..."
mysql --host=$TARGET_HOST --user=$TARGET_USER --password=$TARGET_PASS \
  $TARGET_DB < schema.sql

# Step 3: Export data
echo "Exporting data..."
mysqldump --host=$SOURCE_HOST --user=$SOURCE_USER --password=$SOURCE_PASS \
  --single-transaction --no-create-info --skip-triggers $SOURCE_DB > data.sql

# Step 4: Import data to RDS
echo "Importing data to RDS..."
mysql --host=$TARGET_HOST --user=$TARGET_USER --password=$TARGET_PASS \
  $TARGET_DB < data.sql

# Step 5: Verify migration
echo "Verifying migration..."
SOURCE_COUNT=$(mysql --host=$SOURCE_HOST --user=$SOURCE_USER --password=$SOURCE_PASS \
  -e "SELECT COUNT(*) FROM users;" $SOURCE_DB | tail -1)
TARGET_COUNT=$(mysql --host=$TARGET_HOST --user=$TARGET_USER --password=$TARGET_PASS \
  -e "SELECT COUNT(*) FROM users;" $TARGET_DB | tail -1)

if [ "$SOURCE_COUNT" -eq "$TARGET_COUNT" ]; then
    echo "Migration successful! User count matches: $SOURCE_COUNT"
else
    echo "Migration failed! Count mismatch - Source: $SOURCE_COUNT, Target: $TARGET_COUNT"
    exit 1
fi

echo "Database migration completed successfully!"
```

## Infrastructure Setup

### Using Terraform for Infrastructure as Code

#### 1. Main Infrastructure Configuration
```hcl
# main.tf
terraform {
  required_version = ">= 1.0"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
  
  backend "s3" {
    bucket = "creams-terraform-state"
    key    = "production/terraform.tfstate"
    region = "us-east-1"
  }
}

provider "aws" {
  region = var.aws_region
  
  default_tags {
    tags = {
      Application = "CREAMS"
      Environment = var.environment
      ManagedBy   = "Terraform"
    }
  }
}

# Variables
variable "aws_region" {
  description = "AWS region"
  type        = string
  default     = "us-east-1"
}

variable "environment" {
  description = "Environment name"
  type        = string
  default     = "production"
}

variable "app_name" {
  description = "Application name"
  type        = string
  default     = "creams"
}
```

#### 2. VPC and Networking
```hcl
# vpc.tf
resource "aws_vpc" "main" {
  cidr_block           = "10.0.0.0/16"
  enable_dns_hostnames = true
  enable_dns_support   = true
  
  tags = {
    Name = "${var.app_name}-vpc"
  }
}

# Internet Gateway
resource "aws_internet_gateway" "main" {
  vpc_id = aws_vpc.main.id
  
  tags = {
    Name = "${var.app_name}-igw"
  }
}

# Public Subnets
resource "aws_subnet" "public" {
  count             = 2
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.${count.index + 1}.0/24"
  availability_zone = data.aws_availability_zones.available.names[count.index]
  
  map_public_ip_on_launch = true
  
  tags = {
    Name = "${var.app_name}-public-subnet-${count.index + 1}"
    Type = "Public"
  }
}

# Private Subnets
resource "aws_subnet" "private" {
  count             = 2
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.${count.index + 3}.0/24"
  availability_zone = data.aws_availability_zones.available.names[count.index]
  
  tags = {
    Name = "${var.app_name}-private-subnet-${count.index + 1}"
    Type = "Private"
  }
}

# Route Tables
resource "aws_route_table" "public" {
  vpc_id = aws_vpc.main.id
  
  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.main.id
  }
  
  tags = {
    Name = "${var.app_name}-public-rt"
  }
}

# Associate public subnets with route table
resource "aws_route_table_association" "public" {
  count          = 2
  subnet_id      = aws_subnet.public[count.index].id
  route_table_id = aws_route_table.public.id
}

# Data source for availability zones
data "aws_availability_zones" "available" {
  state = "available"
}
```

#### 3. Security Groups
```hcl
# security-groups.tf

# ALB Security Group
resource "aws_security_group" "alb" {
  name_prefix = "${var.app_name}-alb-"
  vpc_id      = aws_vpc.main.id
  
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  ingress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  tags = {
    Name = "${var.app_name}-alb-sg"
  }
}

# ECS Security Group
resource "aws_security_group" "ecs" {
  name_prefix = "${var.app_name}-ecs-"
  vpc_id      = aws_vpc.main.id
  
  ingress {
    from_port       = 80
    to_port         = 80
    protocol        = "tcp"
    security_groups = [aws_security_group.alb.id]
  }
  
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  tags = {
    Name = "${var.app_name}-ecs-sg"
  }
}

# RDS Security Group
resource "aws_security_group" "rds" {
  name_prefix = "${var.app_name}-rds-"
  vpc_id      = aws_vpc.main.id
  
  ingress {
    from_port       = 3306
    to_port         = 3306
    protocol        = "tcp"
    security_groups = [aws_security_group.ecs.id]
  }
  
  tags = {
    Name = "${var.app_name}-rds-sg"
  }
}

# Redis Security Group
resource "aws_security_group" "redis" {
  name_prefix = "${var.app_name}-redis-"
  vpc_id      = aws_vpc.main.id
  
  ingress {
    from_port       = 6379
    to_port         = 6379
    protocol        = "tcp"
    security_groups = [aws_security_group.ecs.id]
  }
  
  tags = {
    Name = "${var.app_name}-redis-sg"
  }
}
```

#### 4. RDS Database
```hcl
# rds.tf

# DB Subnet Group
resource "aws_db_subnet_group" "main" {
  name       = "${var.app_name}-db-subnet-group"
  subnet_ids = aws_subnet.private[*].id
  
  tags = {
    Name = "${var.app_name}-db-subnet-group"
  }
}

# RDS Instance
resource "aws_db_instance" "main" {
  identifier = "${var.app_name}-production"
  
  # Engine
  engine         = "mysql"
  engine_version = "8.0.35"
  
  # Instance
  instance_class    = "db.t3.medium"
  allocated_storage = 100
  max_allocated_storage = 1000
  storage_type      = "gp2"
  storage_encrypted = true
  
  # Database
  db_name  = "creams"
  username = "admin"
  password = var.db_password # Set via terraform.tfvars
  
  # Network & Security
  db_subnet_group_name   = aws_db_subnet_group.main.name
  vpc_security_group_ids = [aws_security_group.rds.id]
  publicly_accessible    = false
  
  # Backup & Maintenance
  backup_retention_period   = 7
  backup_window            = "03:00-04:00"
  maintenance_window       = "sun:04:00-sun:05:00"
  auto_minor_version_upgrade = true
  
  # High Availability
  multi_az = true
  
  # Monitoring
  monitoring_interval = 60
  monitoring_role_arn = aws_iam_role.rds_monitoring.arn
  
  # Performance Insights
  performance_insights_enabled = true
  performance_insights_retention_period = 7
  
  # Protection
  deletion_protection = true
  skip_final_snapshot = false
  final_snapshot_identifier = "${var.app_name}-final-snapshot"
  
  tags = {
    Name = "${var.app_name}-database"
  }
}

# RDS Monitoring IAM Role
resource "aws_iam_role" "rds_monitoring" {
  name = "${var.app_name}-rds-monitoring-role"
  
  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action = "sts:AssumeRole"
        Effect = "Allow"
        Principal = {
          Service = "monitoring.rds.amazonaws.com"
        }
      }
    ]
  })
}

resource "aws_iam_role_policy_attachment" "rds_monitoring" {
  role       = aws_iam_role.rds_monitoring.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonRDSEnhancedMonitoringRole"
}
```

#### 5. ElastiCache Redis
```hcl
# elasticache.tf

# Redis Subnet Group
resource "aws_elasticache_subnet_group" "main" {
  name       = "${var.app_name}-redis-subnet-group"
  subnet_ids = aws_subnet.private[*].id
}

# Redis Cluster
resource "aws_elasticache_replication_group" "main" {
  replication_group_id       = "${var.app_name}-redis"
  description                = "Redis cluster for ${var.app_name}"
  
  node_type                  = "cache.t3.micro"
  port                       = 6379
  parameter_group_name       = "default.redis7"
  
  num_cache_clusters         = 2
  automatic_failover_enabled = true
  multi_az_enabled          = true
  
  subnet_group_name = aws_elasticache_subnet_group.main.name
  security_group_ids = [aws_security_group.redis.id]
  
  at_rest_encryption_enabled = true
  transit_encryption_enabled = true
  
  tags = {
    Name = "${var.app_name}-redis"
  }
}
```

#### 6. Application Load Balancer
```hcl
# alb.tf

resource "aws_lb" "main" {
  name               = "${var.app_name}-alb"
  internal           = false
  load_balancer_type = "application"
  security_groups    = [aws_security_group.alb.id]
  subnets           = aws_subnet.public[*].id
  
  enable_deletion_protection = true
  
  tags = {
    Name = "${var.app_name}-alb"
  }
}

# Target Group
resource "aws_lb_target_group" "main" {
  name        = "${var.app_name}-tg"
  port        = 80
  protocol    = "HTTP"
  vpc_id      = aws_vpc.main.id
  target_type = "ip"
  
  health_check {
    enabled             = true
    healthy_threshold   = 2
    unhealthy_threshold = 2
    timeout             = 5
    interval            = 30
    path                = "/health"
    matcher             = "200"
    port                = "traffic-port"
    protocol            = "HTTP"
  }
  
  tags = {
    Name = "${var.app_name}-tg"
  }
}

# HTTP Listener (redirect to HTTPS)
resource "aws_lb_listener" "http" {
  load_balancer_arn = aws_lb.main.arn
  port              = "80"
  protocol          = "HTTP"
  
  default_action {
    type = "redirect"
    
    redirect {
      port        = "443"
      protocol    = "HTTPS"
      status_code = "HTTP_301"
    }
  }
}

# HTTPS Listener
resource "aws_lb_listener" "https" {
  load_balancer_arn = aws_lb.main.arn
  port              = "443"
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-TLS-1-2-2017-01"
  certificate_arn   = aws_acm_certificate_validation.main.certificate_arn
  
  default_action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.main.arn
  }
}
```

#### 7. ECS Cluster and Service
```hcl
# ecs.tf

# ECS Cluster
resource "aws_ecs_cluster" "main" {
  name = "${var.app_name}-cluster"
  
  configuration {
    execute_command_configuration {
      logging = "OVERRIDE"
      
      log_configuration {
        cloud_watch_encryption_enabled = true
        cloud_watch_log_group_name     = aws_cloudwatch_log_group.ecs.name
      }
    }
  }
  
  tags = {
    Name = "${var.app_name}-cluster"
  }
}

# ECR Repository
resource "aws_ecr_repository" "main" {
  name = var.app_name
  
  image_tag_mutability = "MUTABLE"
  
  image_scanning_configuration {
    scan_on_push = true
  }
  
  tags = {
    Name = "${var.app_name}-ecr"
  }
}

# ECS Task Definition
resource "aws_ecs_task_definition" "main" {
  family                   = var.app_name
  network_mode             = "awsvpc"
  requires_compatibilities = ["FARGATE"]
  cpu                      = "512"
  memory                   = "1024"
  execution_role_arn       = aws_iam_role.ecs_execution.arn
  task_role_arn           = aws_iam_role.ecs_task.arn
  
  container_definitions = jsonencode([
    {
      name  = var.app_name
      image = "${aws_ecr_repository.main.repository_url}:latest"
      
      portMappings = [
        {
          containerPort = 80
          protocol      = "tcp"
        }
      ]
      
      environment = [
        {
          name  = "APP_ENV"
          value = "production"
        },
        {
          name  = "APP_DEBUG"
          value = "false"
        }
      ]
      
      secrets = [
        {
          name      = "APP_KEY"
          valueFrom = aws_ssm_parameter.app_key.arn
        },
        {
          name      = "DB_HOST"
          valueFrom = aws_ssm_parameter.db_host.arn
        },
        {
          name      = "DB_PASSWORD"
          valueFrom = aws_ssm_parameter.db_password.arn
        }
      ]
      
      logConfiguration = {
        logDriver = "awslogs"
        options = {
          awslogs-group         = aws_cloudwatch_log_group.ecs.name
          awslogs-region        = var.aws_region
          awslogs-stream-prefix = "ecs"
        }
      }
      
      essential = true
    }
  ])
  
  tags = {
    Name = "${var.app_name}-task-definition"
  }
}

# ECS Service
resource "aws_ecs_service" "main" {
  name            = "${var.app_name}-service"
  cluster         = aws_ecs_cluster.main.id
  task_definition = aws_ecs_task_definition.main.arn
  desired_count   = 2
  launch_type     = "FARGATE"
  
  network_configuration {
    security_groups = [aws_security_group.ecs.id]
    subnets         = aws_subnet.private[*].id
  }
  
  load_balancer {
    target_group_arn = aws_lb_target_group.main.arn
    container_name   = var.app_name
    container_port   = 80
  }
  
  depends_on = [aws_lb_listener.https]
  
  tags = {
    Name = "${var.app_name}-service"
  }
}
```

#### 8. S3 and CloudFront
```hcl
# s3-cloudfront.tf

# S3 Bucket for static assets
resource "aws_s3_bucket" "static_assets" {
  bucket = "${var.app_name}-static-assets"
  
  tags = {
    Name = "${var.app_name}-static-assets"
  }
}

# S3 Bucket for file uploads
resource "aws_s3_bucket" "uploads" {
  bucket = "${var.app_name}-uploads"
  
  tags = {
    Name = "${var.app_name}-uploads"
  }
}

# S3 Bucket versioning
resource "aws_s3_bucket_versioning" "uploads" {
  bucket = aws_s3_bucket.uploads.id
  versioning_configuration {
    status = "Enabled"
  }
}

# S3 Bucket encryption
resource "aws_s3_bucket_server_side_encryption_configuration" "uploads" {
  bucket = aws_s3_bucket.uploads.id
  
  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "AES256"
    }
  }
}

# CloudFront Distribution
resource "aws_cloudfront_distribution" "main" {
  origin {
    domain_name = aws_s3_bucket.static_assets.bucket_regional_domain_name
    origin_id   = "S3-${aws_s3_bucket.static_assets.bucket}"
    
    s3_origin_config {
      origin_access_identity = aws_cloudfront_origin_access_identity.main.cloudfront_access_identity_path
    }
  }
  
  # Additional origin for ALB
  origin {
    domain_name = aws_lb.main.dns_name
    origin_id   = "ALB-${aws_lb.main.name}"
    
    custom_origin_config {
      http_port              = 80
      https_port             = 443
      origin_protocol_policy = "https-only"
      origin_ssl_protocols   = ["TLSv1.2"]
    }
  }
  
  enabled = true
  
  # Default cache behavior for application
  default_cache_behavior {
    allowed_methods        = ["DELETE", "GET", "HEAD", "OPTIONS", "PATCH", "POST", "PUT"]
    cached_methods         = ["GET", "HEAD"]
    target_origin_id       = "ALB-${aws_lb.main.name}"
    
    forwarded_values {
      query_string = true
      headers      = ["*"]
      
      cookies {
        forward = "all"
      }
    }
    
    viewer_protocol_policy = "redirect-to-https"
    min_ttl                = 0
    default_ttl            = 0
    max_ttl                = 0
  }
  
  # Cache behavior for static assets
  ordered_cache_behavior {
    path_pattern     = "/css/*"
    allowed_methods  = ["GET", "HEAD"]
    cached_methods   = ["GET", "HEAD"]
    target_origin_id = "S3-${aws_s3_bucket.static_assets.bucket}"
    
    forwarded_values {
      query_string = false
      
      cookies {
        forward = "none"
      }
    }
    
    viewer_protocol_policy = "redirect-to-https"
    min_ttl                = 86400
    default_ttl            = 86400
    max_ttl                = 31536000
  }
  
  restrictions {
    geo_restriction {
      restriction_type = "none"
    }
  }
  
  viewer_certificate {
    acm_certificate_arn = aws_acm_certificate_validation.main.certificate_arn
    ssl_support_method  = "sni-only"
  }
  
  tags = {
    Name = "${var.app_name}-cloudfront"
  }
}

resource "aws_cloudfront_origin_access_identity" "main" {
  comment = "OAI for ${var.app_name}"
}
```

## Application Deployment

### Docker Configuration

#### 1. Dockerfile for Production
```dockerfile
# Dockerfile
FROM php:8.1-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Clear cache
RUN apk del --purge *-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www:www . /var/www/html

# Install dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Set proper permissions
RUN chown -R www:www /var/www/html
RUN chmod -R 755 /var/www/html/storage
RUN chmod -R 755 /var/www/html/bootstrap/cache

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/conf.d/default.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy startup script
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Expose port 80
EXPOSE 80

# Start supervisor
CMD ["/usr/local/bin/start.sh"]
```

#### 2. Nginx Configuration
```nginx
# docker/nginx.conf
user www;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';
    
    access_log /var/log/nginx/access.log main;
    
    sendfile on;
    keepalive_timeout 65;
    
    include /etc/nginx/conf.d/*.conf;
}
```

```nginx
# docker/default.conf
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Increase timeout for long-running requests
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Health check endpoint
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
}
```

#### 3. Supervisor Configuration
```ini
# docker/supervisord.conf
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/php-fpm.err.log
stdout_logfile=/var/log/supervisor/php-fpm.out.log
user=www

[program:nginx]
command=nginx -g 'daemon off;'
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/nginx.err.log
stdout_logfile=/var/log/supervisor/nginx.out.log
user=root

[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/laravel-worker.log
stopwaitsecs=3600
```

#### 4. Startup Script
```bash
#!/bin/sh
# docker/start.sh

echo "Starting CREAMS application..."

# Wait for database to be ready
echo "Waiting for database..."
while ! nc -z $DB_HOST 3306; do
  sleep 1
done
echo "Database is ready!"

# Run Laravel optimization commands
echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Create storage link
echo "Creating storage link..."
php artisan storage:link

# Set proper permissions
chown -R www:www /var/www/html/storage
chown -R www:www /var/www/html/bootstrap/cache

echo "Starting supervisord..."
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
```

### CI/CD Pipeline with GitHub Actions

#### 1. Build and Deploy Workflow
```yaml
# .github/workflows/deploy-aws.yml
name: Deploy to AWS ECS

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

env:
  AWS_REGION: us-east-1
  ECR_REPOSITORY: creams
  ECS_SERVICE: creams-service
  ECS_CLUSTER: creams-cluster
  ECS_TASK_DEFINITION: creams

jobs:
  deploy:
    name: Deploy to AWS
    runs-on: ubuntu-latest
    environment: production

    steps:
    - name: Checkout
      uses: actions/checkout@v3

    - name: Configure AWS credentials
      uses: aws-actions/configure-aws-credentials@v2
      with:
        aws-access-key-id: ${{ secrets.AWS_ACCESS_KEY_ID }}
        aws-secret-access-key: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
        aws-region: ${{ env.AWS_REGION }}

    - name: Login to Amazon ECR
      id: login-ecr
      uses: aws-actions/amazon-ecr-login@v1

    - name: Build, tag, and push image to Amazon ECR
      id: build-image
      env:
        ECR_REGISTRY: ${{ steps.login-ecr.outputs.registry }}
        IMAGE_TAG: ${{ github.sha }}
      run: |
        # Build Docker image
        docker build -t $ECR_REGISTRY/$ECR_REPOSITORY:$IMAGE_TAG .
        docker build -t $ECR_REGISTRY/$ECR_REPOSITORY:latest .
        
        # Push Docker image to ECR
        docker push $ECR_REGISTRY/$ECR_REPOSITORY:$IMAGE_TAG
        docker push $ECR_REGISTRY/$ECR_REPOSITORY:latest
        
        echo "image=$ECR_REGISTRY/$ECR_REPOSITORY:$IMAGE_TAG" >> $GITHUB_OUTPUT

    - name: Download task definition
      run: |
        aws ecs describe-task-definition --task-definition ${{ env.ECS_TASK_DEFINITION }} \
          --query taskDefinition > task-definition.json

    - name: Fill in the new image ID in the Amazon ECS task definition
      id: task-def
      uses: aws-actions/amazon-ecs-render-task-definition@v1
      with:
        task-definition: task-definition.json
        container-name: ${{ env.ECR_REPOSITORY }}
        image: ${{ steps.build-image.outputs.image }}

    - name: Deploy Amazon ECS task definition
      uses: aws-actions/amazon-ecs-deploy-task-definition@v1
      with:
        task-definition: ${{ steps.task-def.outputs.task-definition }}
        service: ${{ env.ECS_SERVICE }}
        cluster: ${{ env.ECS_CLUSTER }}
        wait-for-service-stability: true

    - name: Run database migrations
      run: |
        # Get the task ARN of a running task
        TASK_ARN=$(aws ecs list-tasks --cluster ${{ env.ECS_CLUSTER }} \
          --service-name ${{ env.ECS_SERVICE }} \
          --query 'taskArns[0]' --output text)
        
        # Run migration command
        aws ecs execute-command \
          --cluster ${{ env.ECS_CLUSTER }} \
          --task $TASK_ARN \
          --container ${{ env.ECR_REPOSITORY }} \
          --interactive \
          --command "php artisan migrate --force"
```

#### 2. Environment Variables Management
```yaml
# .github/workflows/setup-secrets.yml
name: Setup AWS Secrets

on:
  workflow_dispatch:

jobs:
  setup-secrets:
    runs-on: ubuntu-latest
    steps:
    - name: Configure AWS credentials
      uses: aws-actions/configure-aws-credentials@v2
      with:
        aws-access-key-id: ${{ secrets.AWS_ACCESS_KEY_ID }}
        aws-secret-access-key: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
        aws-region: us-east-1

    - name: Create SSM Parameters
      run: |
        # Laravel App Key
        aws ssm put-parameter \
          --name "/creams/app/key" \
          --value "${{ secrets.LARAVEL_APP_KEY }}" \
          --type "SecureString" \
          --overwrite

        # Database credentials
        aws ssm put-parameter \
          --name "/creams/db/host" \
          --value "creams-production.cluster-xyz.us-east-1.rds.amazonaws.com" \
          --type "SecureString" \
          --overwrite

        aws ssm put-parameter \
          --name "/creams/db/password" \
          --value "${{ secrets.DB_PASSWORD }}" \
          --type "SecureString" \
          --overwrite

        # Redis endpoint
        aws ssm put-parameter \
          --name "/creams/redis/endpoint" \
          --value "creams-redis.xyz.cache.amazonaws.com" \
          --type "SecureString" \
          --overwrite
```

## Migration from Vercel to AWS

### Pre-Migration Checklist
- [ ] Complete infrastructure setup on AWS
- [ ] Database migrated and tested
- [ ] Application deployed and tested on AWS
- [ ] DNS records prepared (but not switched)
- [ ] SSL certificates provisioned
- [ ] Monitoring and alerting configured
- [ ] Backup strategy implemented
- [ ] Rollback plan documented

### Migration Process

#### 1. Parallel Environment Setup
```bash
# 1. Set up AWS infrastructure
terraform init
terraform plan -var="environment=production"
terraform apply -var="environment=production"

# 2. Deploy application to AWS
docker build -t creams:latest .
aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin ACCOUNT.dkr.ecr.us-east-1.amazonaws.com
docker tag creams:latest ACCOUNT.dkr.ecr.us-east-1.amazonaws.com/creams:latest
docker push ACCOUNT.dkr.ecr.us-east-1.amazonaws.com/creams:latest

# 3. Update ECS service
aws ecs update-service --cluster creams-cluster --service creams-service --force-new-deployment
```

#### 2. Data Synchronization
```bash
# Create data sync script for ongoing sync during migration
#!/bin/bash
# sync-data.sh

SOURCE_DB="source-db-connection"
TARGET_DB="aws-rds-connection"

echo "Starting incremental sync..."

# Sync only recent changes (last 24 hours)
SYNC_TIMESTAMP=$(date -d "24 hours ago" '+%Y-%m-%d %H:%M:%S')

# Tables to sync
TABLES=("users" "trainees" "activities" "contact_messages" "letters")

for table in "${TABLES[@]}"; do
    echo "Syncing $table..."
    
    # Export recent changes
    mysqldump --host=$SOURCE_HOST --user=$SOURCE_USER --password=$SOURCE_PASS \
      --single-transaction \
      --where="updated_at >= '$SYNC_TIMESTAMP'" \
      $SOURCE_DB $table > ${table}_sync.sql
    
    # Import to AWS RDS
    mysql --host=$TARGET_HOST --user=$TARGET_USER --password=$TARGET_PASS \
      $TARGET_DB < ${table}_sync.sql
done

echo "Sync completed!"
```

#### 3. DNS Switchover
```bash
# Update DNS records to point to AWS
# This should be done during a maintenance window

# Before: Points to Vercel
# your-domain.com CNAME xyz.vercel-dns.com

# After: Points to AWS CloudFront
# your-domain.com CNAME d123456789.cloudfront.net

# Gradual rollover using weighted routing
aws route53 change-resource-record-sets --hosted-zone-id Z123456789 --change-batch '{
  "Changes": [{
    "Action": "CREATE",
    "ResourceRecordSet": {
      "Name": "your-domain.com",
      "Type": "A",
      "SetIdentifier": "aws-production",
      "Weight": 10,
      "AliasTarget": {
        "DNSName": "d123456789.cloudfront.net",
        "EvaluateTargetHealth": false,
        "HostedZoneId": "Z2FDTNDATAQYW2"
      }
    }
  }]
}'
```

#### 4. Traffic Migration Strategy
```yaml
# Gradual traffic migration plan
Week 1: 10% AWS, 90% Vercel
Week 2: 25% AWS, 75% Vercel  
Week 3: 50% AWS, 50% Vercel
Week 4: 75% AWS, 25% Vercel
Week 5: 100% AWS, 0% Vercel

# Monitor key metrics during each phase:
# - Response times
# - Error rates
# - Database performance
# - User sessions
# - File upload success rates
```

### Post-Migration Tasks

#### 1. Vercel Cleanup
```bash
# After successful migration to AWS
# Keep Vercel deployment as backup for 30 days

# Scale down Vercel deployment
# Update Vercel environment to "maintenance mode"
vercel env add MAINTENANCE_MODE true

# After 30 days of stable AWS operation:
# Delete Vercel project
vercel remove
```

#### 2. Monitoring Verification
```bash
# Verify all monitoring is working
aws cloudwatch get-dashboard --dashboard-name "CREAMS-Production"
aws logs describe-log-groups --log-group-name-prefix "/aws/ecs/creams"

# Test alerting
aws sns publish --topic-arn "arn:aws:sns:us-east-1:123456789012:creams-alerts" \
  --message "Test alert - CREAMS migration completed successfully"
```

## Monitoring and Logging

### CloudWatch Configuration
```hcl
# monitoring.tf

# CloudWatch Log Groups
resource "aws_cloudwatch_log_group" "ecs" {
  name              = "/aws/ecs/${var.app_name}"
  retention_in_days = 30
  
  tags = {
    Name = "${var.app_name}-ecs-logs"
  }
}

# CloudWatch Dashboard
resource "aws_cloudwatch_dashboard" "main" {
  dashboard_name = "${var.app_name}-dashboard"
  
  dashboard_body = jsonencode({
    widgets = [
      {
        type   = "metric"
        x      = 0
        y      = 0
        width  = 12
        height = 6
        
        properties = {
          metrics = [
            ["AWS/ECS", "CPUUtilization", "ServiceName", "${var.app_name}-service"],
            [".", "MemoryUtilization", ".", "."]
          ]
          period = 300
          stat   = "Average"
          region = var.aws_region
          title  = "ECS Service Metrics"
        }
      },
      {
        type   = "metric"
        x      = 0
        y      = 6
        width  = 12
        height = 6
        
        properties = {
          metrics = [
            ["AWS/RDS", "DatabaseConnections", "DBInstanceIdentifier", aws_db_instance.main.id],
            [".", "CPUUtilization", ".", "."],
            [".", "ReadLatency", ".", "."],
            [".", "WriteLatency", ".", "."]
          ]
          period = 300
          stat   = "Average"
          region = var.aws_region
          title  = "RDS Metrics"
        }
      }
    ]
  })
}

# CloudWatch Alarms
resource "aws_cloudwatch_metric_alarm" "high_cpu" {
  alarm_name          = "${var.app_name}-high-cpu"
  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = "2"
  metric_name         = "CPUUtilization"
  namespace           = "AWS/ECS"
  period              = "300"
  statistic           = "Average"
  threshold           = "80"
  alarm_description   = "This metric monitors ECS CPU utilization"
  
  dimensions = {
    ServiceName = "${var.app_name}-service"
    ClusterName = aws_ecs_cluster.main.name
  }
  
  alarm_actions = [aws_sns_topic.alerts.arn]
}

resource "aws_cloudwatch_metric_alarm" "database_cpu" {
  alarm_name          = "${var.app_name}-database-high-cpu"
  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = "2"
  metric_name         = "CPUUtilization"
  namespace           = "AWS/RDS"
  period              = "300"
  statistic           = "Average"
  threshold           = "70"
  alarm_description   = "This metric monitors RDS CPU utilization"
  
  dimensions = {
    DBInstanceIdentifier = aws_db_instance.main.id
  }
  
  alarm_actions = [aws_sns_topic.alerts.arn]
}

# SNS Topic for Alerts
resource "aws_sns_topic" "alerts" {
  name = "${var.app_name}-alerts"
  
  tags = {
    Name = "${var.app_name}-alerts"
  }
}
```

## Security Implementation

### WAF Configuration
```hcl
# waf.tf

resource "aws_wafv2_web_acl" "main" {
  name  = "${var.app_name}-waf"
  scope = "CLOUDFRONT"
  
  default_action {
    allow {}
  }
  
  # AWS Managed Rules
  rule {
    name     = "AWSManagedRulesCommonRuleSet"
    priority = 1
    
    override_action {
      none {}
    }
    
    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesCommonRuleSet"
        vendor_name = "AWS"
      }
    }
    
    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "AWSManagedRulesCommonRuleSetMetric"
      sampled_requests_enabled   = true
    }
  }
  
  # SQL Injection Protection
  rule {
    name     = "AWSManagedRulesSQLiRuleSet"
    priority = 2
    
    override_action {
      none {}
    }
    
    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesSQLiRuleSet"
        vendor_name = "AWS"
      }
    }
    
    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "AWSManagedRulesSQLiRuleSetMetric"
      sampled_requests_enabled   = true
    }
  }
  
  # Rate limiting
  rule {
    name     = "RateLimitRule"
    priority = 3
    
    action {
      block {}
    }
    
    statement {
      rate_based_statement {
        limit              = 2000
        aggregate_key_type = "IP"
      }
    }
    
    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "RateLimitRuleMetric"
      sampled_requests_enabled   = true
    }
  }
  
  tags = {
    Name = "${var.app_name}-waf"
  }
}
```

## Cost Optimization

### Resource Sizing Recommendations

#### Development Environment
```hcl
# Size for development/staging
variable "dev_config" {
  default = {
    ecs_cpu           = "256"
    ecs_memory        = "512"
    rds_instance_type = "db.t3.micro"
    redis_node_type   = "cache.t2.micro"
  }
}
```

#### Production Environment  
```hcl
# Size for production
variable "prod_config" {
  default = {
    ecs_cpu           = "512"
    ecs_memory        = "1024" 
    rds_instance_type = "db.t3.medium"
    redis_node_type   = "cache.t3.small"
  }
}
```

### Cost Monitoring
```hcl
# budget.tf

resource "aws_budgets_budget" "monthly" {
  name         = "${var.app_name}-monthly-budget"
  budget_type  = "COST"
  limit_amount = "100"
  limit_unit   = "USD"
  time_unit    = "MONTHLY"
  
  cost_filters = {
    Tag = ["Application:CREAMS"]
  }
  
  notification {
    comparison_operator        = "GREATER_THAN"
    threshold                 = 80
    threshold_type            = "PERCENTAGE"
    notification_type         = "ACTUAL"
    subscriber_email_addresses = ["admin@your-domain.com"]
  }
}
```

### Estimated Monthly Costs (US East)

| Service | Configuration | Monthly Cost (USD) |
|---------|---------------|-------------------|
| **ECS Fargate** | 2 tasks × 0.5 vCPU, 1GB RAM | $25 |
| **RDS MySQL** | db.t3.medium, Multi-AZ | $70 |
| **ElastiCache Redis** | cache.t3.small | $35 |
| **ALB** | Application Load Balancer | $18 |
| **S3** | 10GB storage, 100GB transfer | $5 |
| **CloudFront** | 100GB transfer | $8 |
| **CloudWatch** | Logs + Metrics | $10 |
| **Route 53** | Hosted zone + queries | $2 |
| **Data Transfer** | Between services | $10 |
| **SSL Certificate** | AWS Certificate Manager | $0 |
| **WAF** | Web Application Firewall | $15 |
| **Total Estimated** | | **~$198/month** |

## Disaster Recovery

### Backup Strategy
```bash
#!/bin/bash
# backup-strategy.sh

# Daily database backup
aws rds create-db-snapshot \
  --db-instance-identifier creams-production \
  --db-snapshot-identifier creams-snapshot-$(date +%Y%m%d)

# Weekly full backup to S3
aws s3 sync /var/www/html/storage s3://creams-backups/storage/$(date +%Y%m%d)/

# Monthly archive to Glacier
aws s3 cp s3://creams-backups/monthly/ s3://creams-backups-glacier/$(date +%Y%m)/ \
  --storage-class GLACIER --recursive
```

### Recovery Procedures
```bash
#!/bin/bash
# disaster-recovery.sh

echo "Starting disaster recovery process..."

# 1. Restore database from latest snapshot
LATEST_SNAPSHOT=$(aws rds describe-db-snapshots \
  --db-instance-identifier creams-production \
  --query 'DBSnapshots[0].DBSnapshotIdentifier' --output text)

echo "Restoring from snapshot: $LATEST_SNAPSHOT"

aws rds restore-db-instance-from-db-snapshot \
  --db-instance-identifier creams-production-restored \
  --db-snapshot-identifier $LATEST_SNAPSHOT

# 2. Update DNS to point to backup region
aws route53 change-resource-record-sets \
  --hosted-zone-id Z123456789 \
  --change-batch file://failover-dns.json

# 3. Scale up ECS service in backup region  
aws ecs update-service \
  --cluster creams-cluster-backup \
  --service creams-service \
  --desired-count 3

echo "Disaster recovery initiated. Monitor AWS console for status."
```

## Maintenance and Updates

### Rolling Updates
```yaml
# Update strategy for zero-downtime deployments
deployment_configuration {
  maximum_percent         = 200
  minimum_healthy_percent = 50
  
  deployment_circuit_breaker {
    enable   = true
    rollback = true
  }
}
```

### Maintenance Windows
```bash
# Schedule maintenance during low-traffic hours
# Suggested: Sundays 02:00-04:00 UTC

# Pre-maintenance checklist:
# - Notify users via in-app notification
# - Create database snapshot
# - Verify rollback procedures
# - Monitor system health
```

### Update Procedure
```bash
#!/bin/bash
# update-procedure.sh

echo "Starting CREAMS update procedure..."

# 1. Create snapshot before update
aws rds create-db-snapshot \
  --db-instance-identifier creams-production \
  --db-snapshot-identifier pre-update-$(date +%Y%m%d-%H%M)

# 2. Update application via CI/CD
git tag -a "v$(date +%Y.%m.%d)" -m "Production deployment $(date)"
git push origin "v$(date +%Y.%m.%d)"

# 3. Monitor deployment
aws ecs wait services-stable \
  --cluster creams-cluster \
  --services creams-service

# 4. Run health checks
curl -f https://your-domain.com/health || {
  echo "Health check failed! Initiating rollback..."
  # Rollback procedure here
  exit 1
}

echo "Update completed successfully!"
```

## Conclusion

This comprehensive guide provides a complete roadmap for migrating CREAMS from Vercel to AWS with enterprise-grade infrastructure. The architecture delivers:

- **High Availability**: Multi-AZ deployment across availability zones
- **Scalability**: Auto-scaling capabilities for varying load
- **Security**: WAF, VPC, encryption, and security groups
- **Performance**: CDN, caching, and optimized database configuration  
- **Cost Efficiency**: Right-sized resources with monitoring and budgets
- **Reliability**: Automated backups and disaster recovery procedures
- **Maintainability**: Infrastructure as Code and CI/CD pipelines

The migration process is designed to minimize downtime through parallel environment setup and gradual traffic shifting, ensuring a smooth transition from Vercel to AWS.

**Next Steps:**
1. Review and customize infrastructure configurations for your specific needs
2. Set up AWS account and IAM permissions
3. Deploy infrastructure using Terraform
4. Test application deployment in staging environment
5. Execute migration plan during maintenance window
6. Monitor and optimize performance post-migration

For questions or support during the migration process, refer to the AWS documentation or consult with AWS Solution Architects.

---

**Document Version:** 1.0  
**Last Updated:** $(date +%Y-%m-%d)  
**Author:** CREAMS Development Team