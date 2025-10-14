# CREAMS Backend & Database Handling for Vercel

## Overview

This document provides detailed instructions for handling CREAMS Laravel application's backend functionality and database operations when deployed on Vercel. Since Vercel is primarily designed for frontend applications, special considerations are needed for PHP Laravel applications.

## Architecture Overview

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Vercel CDN    │    │  Serverless      │    │   External      │
│   (Frontend)    │───▶│  Functions       │───▶│   Database      │
│   Static Assets │    │  (PHP Runtime)   │    │   (PlanetScale) │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                                │
                                ▼
                       ┌──────────────────┐    ┌─────────────────┐
                       │   File Storage   │    │    Email        │
                       │   (AWS S3/Blob)  │    │   Service       │
                       └──────────────────┘    └─────────────────┘
```

## Database Strategy

### 1. External Database Hosting

#### Option A: PlanetScale (MySQL - Recommended)

**Why PlanetScale?**
- MySQL compatibility with existing CREAMS schema
- Built-in connection pooling
- Branch-based development
- Automatic backups
- Generous free tier

**Setup Process:**
```bash
# Install PlanetScale CLI
pscale auth login

# Create main database
pscale database create creams-production

# Create development branch
pscale branch create creams-production development

# Connect to development branch for testing
pscale connect creams-production development --port 3309

# Connect to main branch for production
pscale connect creams-production main --port 3306
```

**Environment Variables:**
```env
DB_CONNECTION=mysql
DB_HOST=gateway01.us-east-2.psdb.cloud
DB_PORT=3306
DB_DATABASE=creams-production
DB_USERNAME=your-username-from-planetscale
DB_PASSWORD=your-password-from-planetscale
DB_SSLMODE=require
```

#### Option B: Supabase PostgreSQL

**Setup Process:**
```bash
# Create project at supabase.com
# Get connection details from Dashboard > Settings > Database

# Update Laravel for PostgreSQL compatibility
composer require doctrine/dbal
```

**Schema Conversion for PostgreSQL:**
```php
// Update migrations for PostgreSQL compatibility
// In your migrations, change:

// From MySQL:
$table->string('centre_id', 10)->primary();
$table->enum('status', ['active', 'inactive'])->default('active');

// To PostgreSQL:
$table->string('centre_id', 10)->primary();
$table->enum('status', ['active', 'inactive'])->default('active'); // Works in Laravel
```

**Environment Variables:**
```env
DB_CONNECTION=pgsql
DB_HOST=db.your-project-ref.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password
```

### 2. Database Migrations on Vercel

#### Challenge: Vercel Serverless Limitations
- No persistent storage for migration tracking
- Cold starts can timeout long migrations
- Need external migration management

#### Solution A: Pre-deployment Migrations
```bash
# Run migrations before deployment
# Create a separate migration script

#!/bin/bash
# scripts/migrate-production.sh

echo "Starting production migration..."

# Load production environment
export $(grep -v '^#' .env.production | xargs)

# Run migrations against production database
php artisan migrate --force --no-interaction

# Run seeders if needed (be careful in production!)
php artisan db:seed --class=ProductionSeeder --force

echo "Migration completed successfully!"
```

#### Solution B: GitHub Actions Migration
```yaml
# .github/workflows/deploy.yml
name: Deploy to Vercel with Migration

on:
  push:
    branches: [ main ]

jobs:
  migrate:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: pdo, pdo_mysql, mbstring
        
    - name: Install Dependencies
      run: composer install --no-dev --optimize-autoloader
      
    - name: Run Migrations
      run: php artisan migrate --force
      env:
        DB_CONNECTION: ${{ secrets.DB_CONNECTION }}
        DB_HOST: ${{ secrets.DB_HOST }}
        DB_PORT: ${{ secrets.DB_PORT }}
        DB_DATABASE: ${{ secrets.DB_DATABASE }}
        DB_USERNAME: ${{ secrets.DB_USERNAME }}
        DB_PASSWORD: ${{ secrets.DB_PASSWORD }}
        
  deploy:
    needs: migrate
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    - uses: amondnet/vercel-action@v25
      with:
        vercel-token: ${{ secrets.VERCEL_TOKEN }}
        vercel-args: '--prod'
```

### 3. Database Connection Optimization

#### Connection Pooling Configuration
```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        // Connection timeout for serverless
        PDO::ATTR_TIMEOUT => 5,
        // Enable persistent connections
        PDO::ATTR_PERSISTENT => true,
        // Error mode
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]) : [],
],
```

#### Database Health Monitoring
```php
// app/Http/Controllers/HealthController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    public function database()
    {
        try {
            // Test database connection
            $pdo = DB::connection()->getPdo();
            
            // Test basic query
            $result = DB::select('SELECT 1 as test');
            
            // Test table existence
            $tables = DB::select("SHOW TABLES LIKE 'users'");
            
            return response()->json([
                'status' => 'healthy',
                'database_connected' => true,
                'tables_exist' => count($tables) > 0,
                'test_query' => $result[0]->test === 1,
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'database_connected' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }
    
    public function full()
    {
        $checks = [];
        
        // Database check
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'healthy';
        } catch (\Exception $e) {
            $checks['database'] = 'failed: ' . $e->getMessage();
        }
        
        // Cache check
        try {
            Cache::put('health_test', 'ok', 10);
            $cached = Cache::get('health_test');
            $checks['cache'] = $cached === 'ok' ? 'healthy' : 'failed';
        } catch (\Exception $e) {
            $checks['cache'] = 'failed: ' . $e->getMessage();
        }
        
        // Storage check
        try {
            \Storage::disk('default')->put('health_test.txt', 'ok');
            $exists = \Storage::disk('default')->exists('health_test.txt');
            $checks['storage'] = $exists ? 'healthy' : 'failed';
            \Storage::disk('default')->delete('health_test.txt');
        } catch (\Exception $e) {
            $checks['storage'] = 'failed: ' . $e->getMessage();
        }
        
        $overallHealth = !in_array(false, array_map(function($check) {
            return strpos($check, 'healthy') !== false;
        }, $checks));
        
        return response()->json([
            'status' => $overallHealth ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => now()->toISOString(),
        ], $overallHealth ? 200 : 500);
    }
}
```

## Backend Services Architecture

### 1. Serverless Function Structure

#### Main Application Entry Point
```php
// api/index.php - Main entry point for all requests
<?php

// Bootstrap Laravel application for serverless environment
require __DIR__ . '/../public/index.php';
```

#### Specialized Function Endpoints
```php
// api/cron.php - For scheduled tasks
<?php

require __DIR__ . '/../bootstrap/app.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Determine which command to run based on query parameter
$command = $_GET['command'] ?? 'schedule:run';

switch ($command) {
    case 'queue:work':
        $kernel->call('queue:work', ['--stop-when-empty' => true]);
        break;
    case 'schedule:run':
        $kernel->call('schedule:run');
        break;
    default:
        echo "Unknown command: $command";
}

echo "Command executed successfully";
```

```php
// api/webhook.php - For external webhooks
<?php

require __DIR__ . '/../bootstrap/app.php';

// Handle specific webhooks (payment, email, etc.)
$webhook_type = $_GET['type'] ?? 'unknown';

switch ($webhook_type) {
    case 'email':
        // Handle email webhooks
        break;
    case 'payment':
        // Handle payment webhooks  
        break;
    default:
        http_response_code(404);
        echo "Webhook type not found";
}
```

### 2. Session Management

#### Database Sessions (Recommended for Serverless)
```bash
# Create session table
php artisan session:table
php artisan migrate
```

```php
// config/session.php
'driver' => env('SESSION_DRIVER', 'database'),
'connection' => env('SESSION_CONNECTION', null),
'table' => 'sessions',
'store' => env('SESSION_STORE', null),
'lottery' => [2, 100],
'cookie' => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'laravel'), '_').'_session'),
'path' => '/',
'domain' => env('SESSION_DOMAIN', null),
'secure' => env('SESSION_SECURE_COOKIE'),
'http_only' => true,
'same_site' => 'lax',
```

### 3. Cache Management

#### Database Cache for Serverless
```bash
# Create cache table
php artisan cache:table
php artisan migrate
```

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'database'),

'stores' => [
    'database' => [
        'driver' => 'database',
        'table' => 'cache',
        'connection' => null,
        'prefix' => '',
    ],
    
    // Alternative: Redis for better performance
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],
```

### 4. Queue Management

#### Database Queue for Simple Jobs
```bash
# Create jobs table
php artisan queue:table
php artisan migrate
```

```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
    ],
],
```

#### Queue Worker via Cron
```bash
# Add to vercel.json for scheduled execution
{
  "crons": [
    {
      "path": "/api/cron?command=queue:work",
      "schedule": "* * * * *"
    }
  ]
}
```

## File Storage Solutions

### 1. AWS S3 Integration (Recommended)

#### Setup S3 Bucket
```bash
# Create S3 bucket
aws s3 mb s3://creams-production-storage

# Set bucket policy for public assets
aws s3api put-bucket-policy --bucket creams-production-storage --policy file://s3-policy.json
```

```json
// s3-policy.json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PublicReadGetObject",
      "Effect": "Allow",
      "Principal": "*",
      "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::creams-production-storage/public/*"
    }
  ]
}
```

#### Laravel S3 Configuration
```php
// config/filesystems.php
'default' => env('FILESYSTEM_DISK', 's3'),

'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        'throw' => false,
        // Custom configurations for CREAMS
        'options' => [
            'CacheControl' => 'max-age=86400',
            'Metadata' => [
                'uploaded_by' => 'creams-system'
            ]
        ]
    ],
],
```

#### File Upload Handling
```php
// app/Http/Controllers/FileController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'required|in:avatar,document,asset_image',
        ]);

        $file = $request->file('file');
        $type = $request->input('type');
        
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "{$type}s/{$filename}";
        
        // Upload to S3
        $uploaded = Storage::disk('s3')->put($path, file_get_contents($file), 'public');
        
        if ($uploaded) {
            $url = Storage::disk('s3')->url($path);
            
            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => $url,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
        }
        
        return response()->json(['success' => false], 500);
    }
    
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);
        
        $deleted = Storage::disk('s3')->delete($request->path);
        
        return response()->json(['success' => $deleted]);
    }
}
```

### 2. Vercel Blob Storage Alternative

```bash
npm install @vercel/blob
```

```php
// app/Services/VercelBlobService.php
<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;

class VercelBlobService
{
    private $client;
    private $token;
    
    public function __construct()
    {
        $this->token = env('VERCEL_BLOB_READ_WRITE_TOKEN');
        $this->client = new Client();
    }
    
    public function put($path, $contents)
    {
        $response = $this->client->put("https://blob.vercel-storage.com/{$path}", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/octet-stream',
            ],
            'body' => $contents
        ]);
        
        return $response->getStatusCode() === 200;
    }
    
    public function get($path)
    {
        $response = $this->client->get("https://blob.vercel-storage.com/{$path}", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
            ]
        ]);
        
        return $response->getBody()->getContents();
    }
}
```

## Email Service Integration

### 1. SMTP Configuration (Gmail/Outlook)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-specific-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@creams.app
MAIL_FROM_NAME="CREAMS System"
```

### 2. Queue-based Email Processing

```php
// app/Jobs/SendEmailJob.php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $emailData;
    protected $template;
    protected $recipient;

    public function __construct($recipient, $template, $emailData = [])
    {
        $this->recipient = $recipient;
        $this->template = $template;
        $this->emailData = $emailData;
    }

    public function handle()
    {
        Mail::send($this->template, $this->emailData, function ($message) {
            $message->to($this->recipient['email'], $this->recipient['name'])
                   ->subject($this->emailData['subject'] ?? 'CREAMS Notification');
        });
    }
}
```

### 3. Email Testing in Development

```php
// For development, use log driver
// config/mail.php
'default' => env('MAIL_MAILER', 'log'),

'mailers' => [
    'log' => [
        'transport' => 'log',
        'channel' => env('MAIL_LOG_CHANNEL'),
    ],
],
```

## Performance Optimization for Serverless

### 1. Laravel Optimization Commands

```bash
# Run these during build process
php artisan config:cache
php artisan route:cache
php artisan view:cache

# For production
php artisan optimize
```

### 2. Database Query Optimization

```php
// app/Http/Middleware/DatabaseQueryLogger.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseQueryLogger
{
    public function handle($request, Closure $next)
    {
        if (app()->environment('local')) {
            DB::listen(function ($query) {
                if ($query->time > 100) { // Log slow queries
                    Log::warning('Slow Query', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time
                    ]);
                }
            });
        }
        
        return $next($request);
    }
}
```

### 3. Connection Management

```php
// app/Http/Middleware/DatabaseConnectionManager.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionManager
{
    public function handle($request, Closure $next)
    {
        // Ensure clean connection for each serverless request
        try {
            DB::connection()->reconnect();
        } catch (\Exception $e) {
            // Log connection error but don't fail the request
            \Log::error('Database connection failed', ['error' => $e->getMessage()]);
        }
        
        $response = $next($request);
        
        // Clean up connections after request
        DB::disconnect();
        
        return $response;
    }
}
```

## Monitoring and Logging

### 1. Application Logging

```php
// config/logging.php - Configure for Vercel
'default' => env('LOG_CHANNEL', 'stack'),

'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'errorlog'],
        'ignore_exceptions' => false,
    ],

    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],

    'errorlog' => [
        'driver' => 'errorlog',
        'level' => env('LOG_LEVEL', 'debug'),
    ],
],
```

### 2. Performance Monitoring

```php
// app/Http/Middleware/PerformanceMonitor.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class PerformanceMonitor
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = ($endTime - $startTime) * 1000; // milliseconds
        $memoryUsage = ($endMemory - $startMemory) / 1024 / 1024; // MB
        
        if ($executionTime > 1000 || $memoryUsage > 50) {
            Log::info('Performance Alert', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time_ms' => round($executionTime, 2),
                'memory_usage_mb' => round($memoryUsage, 2),
                'user_id' => auth()->id(),
            ]);
        }
        
        // Add performance headers
        $response->headers->set('X-Execution-Time', round($executionTime, 2));
        $response->headers->set('X-Memory-Usage', round($memoryUsage, 2));
        
        return $response;
    }
}
```

## Backup and Recovery

### 1. Database Backup Strategy

```php
// app/Console/Commands/DatabaseBackup.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Create database backup';

    public function handle()
    {
        $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
        
        // Create mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s',
            config('database.connections.mysql.host'),
            config('database.connections.mysql.port'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database')
        );
        
        $process = Process::fromShellCommandline($command);
        $process->run();
        
        if ($process->isSuccessful()) {
            // Upload to S3
            Storage::disk('s3')->put("backups/{$filename}", $process->getOutput());
            $this->info("Backup created successfully: {$filename}");
        } else {
            $this->error('Backup failed: ' . $process->getErrorOutput());
        }
    }
}
```

### 2. Automated Backup Scheduling

```bash
# Add to vercel.json
{
  "crons": [
    {
      "path": "/api/cron?command=db:backup",
      "schedule": "0 2 * * *"
    }
  ]
}
```

## Security Considerations

### 1. Environment Variable Security

```php
// app/Http/Middleware/EnvironmentSecurityCheck.php
<?php

namespace App\Http\Middleware;

use Closure;

class EnvironmentSecurityCheck
{
    public function handle($request, Closure $next)
    {
        // Ensure critical environment variables are set
        $required = [
            'APP_KEY',
            'DB_HOST',
            'DB_USERNAME', 
            'DB_PASSWORD'
        ];
        
        foreach ($required as $var) {
            if (!env($var)) {
                abort(500, "Critical environment variable {$var} not set");
            }
        }
        
        // Verify APP_KEY is properly formatted
        if (!preg_match('/^base64:/', env('APP_KEY'))) {
            abort(500, 'Invalid APP_KEY format');
        }
        
        return $next($request);
    }
}
```

### 2. Database Security

```php
// config/database.php - Add SSL and security options
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => env('MYSQL_ATTR_SSL_VERIFY_SERVER_CERT', true),
        PDO::MYSQL_ATTR_SSL_CIPHER => env('MYSQL_ATTR_SSL_CIPHER'),
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_PERSISTENT => false, // Disable for security
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]) : [],
],
```

## Testing Strategy

### 1. Database Connection Tests

```php
// tests/Feature/DatabaseConnectionTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseConnectionTest extends TestCase
{
    public function test_database_connection()
    {
        // Test basic connection
        $this->assertTrue(DB::connection()->getPdo() !== null);
    }
    
    public function test_database_crud_operations()
    {
        // Test create
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->assertIsNumeric($userId);
        
        // Test read
        $user = DB::table('users')->find($userId);
        $this->assertEquals('Test User', $user->name);
        
        // Test update
        DB::table('users')->where('id', $userId)->update(['name' => 'Updated User']);
        $updatedUser = DB::table('users')->find($userId);
        $this->assertEquals('Updated User', $updatedUser->name);
        
        // Test delete
        DB::table('users')->where('id', $userId)->delete();
        $deletedUser = DB::table('users')->find($userId);
        $this->assertNull($deletedUser);
    }
}
```

### 2. Serverless Function Tests

```php
// tests/Feature/ServerlessFunctionTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ServerlessFunctionTest extends TestCase
{
    public function test_health_endpoint()
    {
        $response = $this->get('/api/health');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'database_connected',
            'timestamp'
        ]);
    }
    
    public function test_main_application_endpoint()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
    }
}
```

## Troubleshooting Common Issues

### 1. Database Connection Issues

**Problem:** "SQLSTATE[HY000] [2002] Connection refused"

**Solutions:**
```bash
# Check environment variables
vercel env ls

# Test database connection locally
php artisan tinker
DB::connection()->getPdo();

# Check PlanetScale connection
pscale connect creams-production main --port 3306
```

### 2. File Upload Issues

**Problem:** Files not uploading to S3

**Solutions:**
```bash
# Verify AWS credentials
aws sts get-caller-identity

# Test S3 connection
php artisan tinker
Storage::disk('s3')->put('test.txt', 'Hello World');
```

### 3. Session Issues

**Problem:** Users getting logged out frequently

**Solutions:**
```php
// Check session configuration
// config/session.php
'lifetime' => env('SESSION_LIFETIME', 120),
'expire_on_close' => false,
'encrypt' => false,
'files' => storage_path('framework/sessions'),
'connection' => env('SESSION_CONNECTION', null),
'table' => 'sessions',
```

### 4. Performance Issues

**Problem:** Slow response times

**Solutions:**
```bash
# Enable query logging
DB::listen(function ($query) {
    Log::info($query->sql, $query->bindings, $query->time);
});

# Check for N+1 queries
# Use eager loading
$users = User::with('centre')->get();
```

## Conclusion

This backend and database handling guide provides comprehensive strategies for successfully deploying CREAMS Laravel application on Vercel. The key points are:

1. **Use external database hosting** (PlanetScale recommended)
2. **Implement proper connection management** for serverless environment
3. **Use database-based sessions and cache** for stateless functions
4. **Implement robust file storage** with S3 or Vercel Blob
5. **Monitor performance and errors** closely
6. **Maintain security** with proper environment variable management

Following these strategies will ensure your CREAMS application runs reliably and performantly on Vercel while maintaining all backend functionality.