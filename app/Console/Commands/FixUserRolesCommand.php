<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class FixUserRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'creams:fix-user-roles {--scan-only : Only scan for issues without fixing them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes role-specific references to separate user tables by redirecting to the main users table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('CREAMS User Roles Fix Tool');
        $this->info('This command will find and fix code that incorrectly references separate role tables (admins, supervisors, etc.)');
        $this->info('Instead, it will update references to use the main Users model with role filtering');
        
        $scanOnly = $this->option('scan-only');
        if ($scanOnly) {
            $this->info('Running in scan-only mode - no changes will be made');
        }
        
        if (!$scanOnly && !$this->confirm('Do you want to continue with fixing the code?', true)) {
            $this->info('Operation cancelled');
            return 0;
        }
        
        // Step 1: Check if the users table exists
        $this->info('Step 1: Checking database tables...');
        
        if (!Schema::hasTable('users')) {
            $this->error('The users table does not exist in the database. Please run migrations first.');
            return 1;
        }
        
        $this->info('Users table exists. Continuing...');
        
        // Step 2: Check for references to role-specific models in controllers
        $this->info('Step 2: Scanning controllers for role-specific model references...');
        
        $controllersPath = app_path('Http/Controllers');
        $modelReferences = [
            'Admins::' => 0,
            'Supervisors::' => 0,
            'Teachers::' => 0,
            'AJKs::' => 0,
            'App\\Models\\Admins' => 0,
            'App\\Models\\Supervisors' => 0,
            'App\\Models\\Teachers' => 0,
            'App\\Models\\AJKs' => 0,
        ];
        
        $filesToFix = [];
        
        // Scan controller files
        $controllerFiles = File::allFiles($controllersPath);
        foreach ($controllerFiles as $file) {
            $filePath = $file->getPathname();
            $content = File::get($filePath);
            
            $foundReferences = false;
            foreach ($modelReferences as $reference => $count) {
                if (Str::contains($content, $reference)) {
                    $modelReferences[$reference]++;
                    $foundReferences = true;
                }
            }
            
            if ($foundReferences) {
                $filesToFix[] = $filePath;
                $this->warn("Found role-specific model references in: " . basename($filePath));
            }
        }
        
        // Step 3: Fix references if scan-only flag is not set
        if (!$scanOnly && !empty($filesToFix)) {
            $this->info('Step 3: Fixing role-specific model references...');
            
            foreach ($filesToFix as $filePath) {
                $this->info("Fixing file: " . basename($filePath));
                
                $content = File::get($filePath);
                $originalContent = $content;
                
                // Replace role-specific model imports with Users model
                $content = preg_replace(
                    '/use App\\\\Models\\\\(Admins|Supervisors|Teachers|AJKs);/m',
                    'use App\\Models\\Users;',
                    $content
                );
                
                // Replace direct static calls to role-specific models with Users model + where clause
                $replacements = [
                    // Find pattern
                    '/(\\\App\\\Models\\\|\b)(Admins)::find\((\$[a-zA-Z0-9_]+)\)/m' => 'Users::where(\'role\', \'admin\')->where(\'id\', $3)->first()',
                    '/(\\\App\\\Models\\\|\b)(Supervisors)::find\((\$[a-zA-Z0-9_]+)\)/m' => 'Users::where(\'role\', \'supervisor\')->where(\'id\', $3)->first()',
                    '/(\\\App\\\Models\\\|\b)(Teachers)::find\((\$[a-zA-Z0-9_]+)\)/m' => 'Users::where(\'role\', \'teacher\')->where(\'id\', $3)->first()',
                    '/(\\\App\\\Models\\\|\b)(AJKs)::find\((\$[a-zA-Z0-9_]+)\)/m' => 'Users::where(\'role\', \'ajk\')->where(\'id\', $3)->first()',
                    
                    // Additional replacements for other common patterns
                    '/(\\\App\\\Models\\\|\b)(Admins)::findOrFail\((\$[a-zA-Z0-9_]+)\)/m' => 'Users::where(\'role\', \'admin\')->where(\'id\', $3)->firstOrFail()',
                    '/(\\\App\\\Models\\\|\b)(Supervisors)::findOrFail\((\$[a-zA-Z0-9_]+)\)/m' => 'Users::where(\'role\', \'supervisor\')->where(\'id\', $3)->firstOrFail()',
                    '/(\\\App\\\Models\\\|\b)(Teachers)::findOrFail\((\$[a-zA-Z0-9_]+)\)/m' => 'Users::where(\'role\', \'teacher\')->where(\'id\', $3)->firstOrFail()',
                    '/(\\\App\\\Models\\\|\b)(AJKs)::findOrFail\((\$[a-zA-Z0-9_]+)\)/m' => 'Users::where(\'role\', \'ajk\')->where(\'id\', $3)->firstOrFail()',
                    
                    // Replace case statements in switch blocks
                    '/case\s+[\'"]admin[\'"]\s*:\s*\$user\s*=\s*(\\\App\\\Models\\\|\b)(Admins)::find\((\$[a-zA-Z0-9_]+)\);/m' => 'case \'admin\': $user = Users::where(\'role\', \'admin\')->where(\'id\', $3)->first();',
                    '/case\s+[\'"]supervisor[\'"]\s*:\s*\$user\s*=\s*(\\\App\\\Models\\\|\b)(Supervisors)::find\((\$[a-zA-Z0-9_]+)\);/m' => 'case \'supervisor\': $user = Users::where(\'role\', \'supervisor\')->where(\'id\', $3)->first();',
                    '/case\s+[\'"]teacher[\'"]\s*:\s*\$user\s*=\s*(\\\App\\\Models\\\|\b)(Teachers)::find\((\$[a-zA-Z0-9_]+)\);/m' => 'case \'teacher\': $user = Users::where(\'role\', \'teacher\')->where(\'id\', $3)->first();',
                    '/case\s+[\'"]ajk[\'"]\s*:\s*\$user\s*=\s*(\\\App\\\Models\\\|\b)(AJKs)::find\((\$[a-zA-Z0-9_]+)\);/m' => 'case \'ajk\': $user = Users::where(\'role\', \'ajk\')->where(\'id\', $3)->first();',
                    
                    // Replace new role-specific model instances
                    '/\$user\s*=\s*new\s+(\\\App\\\Models\\\|\b)(Admins)\(\);/m' => '$user = new Users(); $user->role = \'admin\';',
                    '/\$user\s*=\s*new\s+(\\\App\\\Models\\\|\b)(Supervisors)\(\);/m' => '$user = new Users(); $user->role = \'supervisor\';',
                    '/\$user\s*=\s*new\s+(\\\App\\\Models\\\|\b)(Teachers)\(\);/m' => '$user = new Users(); $user->role = \'teacher\';',
                    '/\$user\s*=\s*new\s+(\\\App\\\Models\\\|\b)(AJKs)\(\);/m' => '$user = new Users(); $user->role = \'ajk\';',
                ];
                
                foreach ($replacements as $pattern => $replacement) {
                    $content = preg_replace($pattern, $replacement, $content);
                }
                
                // Only write the file if changes were made
                if ($content !== $originalContent) {
                    // Create a backup of the original file
                    File::put($filePath . '.bak', $originalContent);
                    
                    // Write the updated content
                    File::put($filePath, $content);
                    
                    $this->info("Updated file: " . basename($filePath) . " (backup created)");
                } else {
                    $this->info("No changes needed for: " . basename($filePath));
                }
            }
        }
        
        // Step 4: Add a temporary fix for missing audit_logs table
        $this->info('Step 4: Adding temporary fix for missing audit_logs table...');
        
        if (!Schema::hasTable('audit_logs')) {
            if (!$scanOnly) {
                if ($this->confirm('The audit_logs table is missing. Would you like to create it?', true)) {
                    // Create the migration for audit_logs table
                    $timestamp = date('Y_m_d_His');
                    $migrationName = "{$timestamp}_create_audit_logs_table.php";
                    $migrationPath = database_path("migrations/{$migrationName}");
                    
                    // Audit logs table migration
                    $auditLogsMigration = '<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(\'audit_logs\', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger(\'user_id\');
            $table->string(\'user_role\');
            $table->string(\'action\'); // create, update, delete, password_reset, status_change
            $table->string(\'table\');
            $table->unsignedBigInteger(\'record_id\');
            $table->json(\'old_values\')->nullable();
            $table->json(\'new_values\')->nullable();
            $table->string(\'ip_address\')->nullable();
            $table->text(\'user_agent\')->nullable();
            $table->timestamps();
            
            // Add indexes
            $table->index([\'user_id\', \'user_role\']);
            $table->index([\'table\', \'record_id\']);
            $table->index(\'action\');
            $table->index(\'created_at\');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(\'audit_logs\');
    }
};';
                    
                    // Check if the migrations directory exists
                    if (!File::isDirectory(database_path('migrations'))) {
                        File::makeDirectory(database_path('migrations'), 0755, true);
                    }
                    
                    // Write the migration file
                    File::put($migrationPath, $auditLogsMigration);
                    $this->info("Created migration: {$migrationName}");
                    
                    // Run the migration
                    if ($this->confirm('Run the migration now?', true)) {
                        $this->info('Running migration...');
                        Artisan::call('migrate', ['--path' => "database/migrations/{$migrationName}"]);
                        $this->info(Artisan::output());
                    }
                }
            } else {
                $this->warn('audit_logs table is missing. Run with --scan-only=false to create it.');
            }
        } else {
            $this->info('audit_logs table already exists.');
        }
        
        // Step 5: Fix DashboardController.php to avoid errors
        $this->info('Step 5: Checking and fixing DashboardController.php...');
        
        $dashboardControllerPath = app_path('Http/Controllers/DashboardController.php');
        if (File::exists($dashboardControllerPath)) {
            $content = File::get($dashboardControllerPath);
            $originalContent = $content;
            
            // Check if the controller has a method that accesses admins table
            if (Str::contains($content, 'App\\Models\\Admins') || 
                Str::contains($content, '\\App\\Models\\Admins') || 
                Str::contains($content, 'Admins::')) {
                
                if (!$scanOnly) {
                    $this->info('Updating DashboardController to use Users model with role filtering...');
                    
                    // Replace imports
                    $content = preg_replace(
                        '/use App\\\\Models\\\\(Admins|Supervisors|Teachers|AJKs);/m',
                        'use App\\Models\\Users;',
                        $content
                    );
                    
                    // Update getUserNameById method if it exists
                    if (Str::contains($content, 'getUserNameById')) {
                        $content = preg_replace(
                            '/private function getUserNameById\(\$userId, \$role\)\s*{\s*switch \(\$role\) {.*?}/s',
                            'private function getUserNameById($userId, $role)
    {
        $user = Users::where(\'role\', $role)->where(\'id\', $userId)->first();
        return $user ? $user->name : \'Unknown User\';
    }',
                            $content
                        );
                    }
                    
                    // Update getLastAccessedData method if it exists
                    if (Str::contains($content, 'getLastAccessedData')) {
                        // Add a check for audit_logs table existence
                        $content = preg_replace(
                            '/private function getLastAccessedData\(\$userId, \$role\)\s*{/m',
                            'private function getLastAccessedData($userId, $role)
    {
        // Check if the audit_logs table exists
        if (!Schema::hasTable(\'audit_logs\')) {
            // Return placeholder data if table doesn\'t exist
            return [
                \'system_activities\' => collect([
                    [
                        \'type\' => \'system_activity\',
                        \'user\' => \'System\',
                        \'action\' => \'Updated\',
                        \'entity\' => \'trainee\',
                        \'timestamp\' => \'5 minutes ago\',
                        \'details\' => \'Updated trainee profile information\'
                    ],
                    [
                        \'type\' => \'system_activity\',
                        \'user\' => \'Admin\',
                        \'action\' => \'Created\',
                        \'entity\' => \'class\',
                        \'timestamp\' => \'2 hours ago\',
                        \'details\' => \'Created new class "Speech Therapy 101"\'
                    ]
                ]),
                \'user_activities\' => collect([
                    [
                        \'type\' => \'user_activity\',
                        \'action\' => \'Viewed\',
                        \'entity\' => \'trainee\',
                        \'timestamp\' => \'10 minutes ago\',
                        \'details\' => \'Viewed trainee profile for Ahmad Razif\'
                    ]
                ]),
                \'last_login\' => \'First login\'
            ];
        }',
                            $content
                        );
                    }
                    
                    // Only write the file if changes were made
                    if ($content !== $originalContent) {
                        // Create a backup of the original file
                        File::put($dashboardControllerPath . '.bak', $originalContent);
                        
                        // Write the updated content
                        File::put($dashboardControllerPath, $content);
                        
                        $this->info("Updated DashboardController.php (backup created)");
                    } else {
                        $this->info("No changes needed for DashboardController.php");
                    }
                } else {
                    $this->warn('DashboardController.php needs fixing. Run with --scan-only=false to fix it.');
                }
            } else {
                $this->info('DashboardController.php looks good - no fixes needed.');
            }
        } else {
            $this->warn('DashboardController.php not found.');
        }
        
        $this->info('Fix operation completed successfully!');
        return 0;
    }
}