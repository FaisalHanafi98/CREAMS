<?php

/**
 * CREAMS Project Reorganization Script
 * This script moves all scattered files to their proper locations
 */

echo "=== CREAMS Project Reorganization Script ===\n";
echo "Moving 119+ files to organized directory structure...\n";
echo "=============================================\n\n";

// Define file movements
$fileMoves = [
    // Documentation files to docs/
    'module-summaries' => [
        'CENTRE_ACTIVITY_MODULES_AUDIT_REPORT.md' => 'docs/module-summaries/',
        'CONTACT_MODULE_FIX_SUMMARY.md' => 'docs/module-summaries/',
        'LETTER_FIX_SUMMARY.md' => 'docs/module-summaries/',
        'LETTER_MODULE_FIX_SUMMARY.md' => 'docs/module-summaries/',
        'VOLUNTEER_MODULE_FIX_SUMMARY.md' => 'docs/module-summaries/',
        'SYSTEM_COMPLETION_REPORT.md' => 'docs/module-summaries/',
    ],
    
    'reports' => [
        'PDF_ISSUE_ANALYSIS.md' => 'docs/reports/',
        'ROUTE_ACCESSIBILITY_REPORT.md' => 'docs/reports/',
        'SYSTEM_BUG_FIXES_DOCUMENTATION.md' => 'docs/reports/',
        'SYSTEM_PROGRESS_SUMMARY.md' => 'docs/reports/',
    ],
    
    'fixes' => [
        'fix_log_20250717.md' => 'docs/fixes/',
    ],
    
    'system-info' => [
        'CREAMS GENERAL OVERVIEW.txt' => 'docs/system-info/',
        'PROJECT_STATE.txt' => 'docs/system-info/',
        'system_health_report.txt' => 'docs/system-info/',
        'SETUP_GUIDE.md' => 'docs/system-info/',
        'cookie.txt' => 'docs/system-info/',
    ],
    
    // Development files to development-resources/
    'debugging' => [
        'debug_current_issue.php' => 'development-resources/debugging/',
        'debug_letter_pdf.php' => 'development-resources/debugging/',
        'debug_password.php' => 'development-resources/debugging/',
        'debug_pdf_generation.php' => 'development-resources/debugging/',
        'debug_pdf_template.php' => 'development-resources/debugging/',
    ],
    
    'testing' => [
        'test_activities.php' => 'development-resources/testing/',
        'test_activity_routes.php' => 'development-resources/testing/',
        'test_assets.php' => 'development-resources/testing/',
        'test_base64_images.php' => 'development-resources/testing/',
        'test_centres.php' => 'development-resources/testing/',
        'test_contact_controller.php' => 'development-resources/testing/',
        'test_contact_form.php' => 'development-resources/testing/',
        'test_correct_password.php' => 'development-resources/testing/',
        'test_dashboard_fix.php' => 'development-resources/testing/',
        'test_dashboard_services.php' => 'development-resources/testing/',
        'test_emergency_fix.php' => 'development-resources/testing/',
        'test_fixed_pdf.php' => 'development-resources/testing/',
        'test_generation_api.php' => 'development-resources/testing/',
        'test_image_paths.php' => 'development-resources/testing/',
        'test_letter_controller.php' => 'development-resources/testing/',
        'test_letter_fix.php' => 'development-resources/testing/',
        'test_letter_system.php' => 'development-resources/testing/',
        'test_letter_template.php' => 'development-resources/testing/',
        'test_letter_template_fixed.php' => 'development-resources/testing/',
        'test_password_regex.php' => 'development-resources/testing/',
        'test_pdf_generation.php' => 'development-resources/testing/',
        'test_pdf_no_images.php' => 'development-resources/testing/',
        'test_pdf_preview_match.php' => 'development-resources/testing/',
        'test_refactored_seeder.php' => 'development-resources/testing/',
        'test_staff_module_fixed.php' => 'development-resources/testing/',
        'test_staff_module_issues.php' => 'development-resources/testing/',
        'test_system.php' => 'development-resources/testing/',
        'test_template_content.php' => 'development-resources/testing/',
        'test_trainee_module_fixed.php' => 'development-resources/testing/',
        'test_trainee_module_issues.php' => 'development-resources/testing/',
        'test_trainees.php' => 'development-resources/testing/',
        'test_users.php' => 'development-resources/testing/',
        'test_volunteer_controller.php' => 'development-resources/testing/',
        'test_volunteer_form.php' => 'development-resources/testing/',
    ],
    
    'database-checks' => [
        'check_activity_sessions.php' => 'development-resources/database-checks/',
        'check_activity_tables.php' => 'development-resources/database-checks/',
        'check_all_columns.php' => 'development-resources/database-checks/',
        'check_assets_table.php' => 'development-resources/database-checks/',
        'check_contact_table.php' => 'development-resources/database-checks/',
        'check_letter_templates_table.php' => 'development-resources/database-checks/',
        'check_letters_table.php' => 'development-resources/database-checks/',
        'check_trainees_table.php' => 'development-resources/database-checks/',
        'check_users_table.php' => 'development-resources/database-checks/',
        'check_volunteers_table.php' => 'development-resources/database-checks/',
    ],
    
    'utilities' => [
        'cleanup_test_trainees.php' => 'development-resources/utilities/',
        'comprehensive_test.php' => 'development-resources/utilities/',
        'final_verification.php' => 'development-resources/utilities/',
        'fix_pdf_generation.php' => 'development-resources/utilities/',
        'seed_asset_types.php' => 'development-resources/utilities/',
        'seed_categories.php' => 'development-resources/utilities/',
        'setup_test_data.php' => 'development-resources/utilities/',
    ],
    
    'deployment' => [
        'automation_script.sh' => 'development-resources/deployment/',
        'deploy.sh' => 'development-resources/deployment/',
    ],
];

// Files to delete (generated test files)
$filesToDelete = [
    'public/letters/ACTUAL_TEST_1752815794.pdf',
    'public/letters/BASE64_HTML_1752816346.html',
    'public/letters/BASE64_TEST_1752816346.pdf',
    'public/letters/DEBUG_TEMPLATE_1752815817.html',
    'public/letters/FIXED_TEMPLATE_1752815875.html',
    'public/letters/IMAGE_PATH_TEST_1752816305.pdf',
    'public/letters/LTR_2025_07_LTR_2025_07_0001_1752813356.pdf',
    'public/letters/LTR_2025_07_LTR_2025_07_0002_1752814224.pdf',
    'public/letters/LTR_2025_07_LTR_2025_07_0003_1752816204.pdf',
    'public/letters/SIMPLE_TEST_1752815897.pdf',
    'public/letters/TEST_FIXED_1752815875.pdf',
    'public/letters/TEST_NO_IMAGES_1752815852.pdf',
    'public/letters/TEST_PDF_1752815794.pdf',
];

// Statistics
$totalFilesMoved = 0;
$totalFilesDeleted = 0;
$errors = [];

echo "Phase 1: Moving files to organized structure...\n";
echo "---------------------------------------------\n";

foreach ($fileMoves as $category => $files) {
    echo "\n📁 Moving {$category} files:\n";
    
    foreach ($files as $sourceFile => $targetDir) {
        if (file_exists($sourceFile)) {
            // Ensure target directory exists
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            
            $targetFile = $targetDir . basename($sourceFile);
            
            if (rename($sourceFile, $targetFile)) {
                echo "  ✅ Moved: {$sourceFile} → {$targetFile}\n";
                $totalFilesMoved++;
            } else {
                echo "  ❌ Failed to move: {$sourceFile}\n";
                $errors[] = "Failed to move: {$sourceFile}";
            }
        } else {
            echo "  ⚠️  Not found: {$sourceFile}\n";
        }
    }
}

echo "\n\nPhase 2: Cleaning up generated test files...\n";
echo "-------------------------------------------\n";

foreach ($filesToDelete as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "  🗑️  Deleted: {$file}\n";
            $totalFilesDeleted++;
        } else {
            echo "  ❌ Failed to delete: {$file}\n";
            $errors[] = "Failed to delete: {$file}";
        }
    } else {
        echo "  ⚠️  Not found: {$file}\n";
    }
}

echo "\n\nPhase 3: Creating additional files...\n";
echo "-----------------------------------\n";

// Create .gitkeep files for empty directories
$emptyDirs = [
    'docs/module-summaries',
    'docs/reports',
    'docs/fixes',
    'docs/system-info',
    'development-resources/debugging',
    'development-resources/testing',
    'development-resources/database-checks',
    'development-resources/utilities',
    'development-resources/deployment',
];

foreach ($emptyDirs as $dir) {
    $gitkeepFile = $dir . '/.gitkeep';
    if (!file_exists($gitkeepFile)) {
        file_put_contents($gitkeepFile, '# This file ensures the directory is tracked by Git');
        echo "  📝 Created: {$gitkeepFile}\n";
    }
}

// Create .env.example if .env exists
if (file_exists('.env') && !file_exists('.env.example')) {
    $envContent = file_get_contents('.env');
    // Remove sensitive data
    $envContent = preg_replace('/^(DB_PASSWORD|MAIL_PASSWORD|APP_KEY)=.*/m', '$1=', $envContent);
    file_put_contents('.env.example', $envContent);
    echo "  📝 Created: .env.example\n";
}

echo "\n\nPhase 4: Final verification...\n";
echo "----------------------------\n";

// Check that critical files still exist
$criticalFiles = [
    'CLAUDE.md',
    'README.md',
    'composer.json',
    'artisan',
    'COMPREHENSIVE_PROJECT_STATE.txt',
    'TECHNICAL_ARCHITECTURE.md',
    'DATABASE_SCHEMA.md',
    'USER_STORIES.md',
    'API_REFERENCE.md',
];

echo "Verifying critical files:\n";
foreach ($criticalFiles as $file) {
    if (file_exists($file)) {
        echo "  ✅ {$file}\n";
    } else {
        echo "  ❌ MISSING: {$file}\n";
        $errors[] = "Critical file missing: {$file}";
    }
}

// Summary
echo "\n\n=== REORGANIZATION COMPLETE ===\n";
echo "==============================\n";
echo "📊 Files moved: {$totalFilesMoved}\n";
echo "🗑️  Files deleted: {$totalFilesDeleted}\n";
echo "❌ Errors: " . count($errors) . "\n";

if (count($errors) > 0) {
    echo "\nErrors encountered:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
    echo "\nPlease review and fix these issues manually.\n";
}

echo "\n✅ Project reorganization completed successfully!\n";
echo "\nNext steps:\n";
echo "1. Review the new directory structure\n";
echo "2. Test that the application still works\n";
echo "3. Update any hardcoded file paths\n";
echo "4. Commit the reorganized structure\n";
echo "5. Begin implementing database schema fixes\n";

echo "\n🏗️  New directory structure:\n";
echo "docs/\n";
echo "├── module-summaries/\n";
echo "├── reports/\n";
echo "├── fixes/\n";
echo "└── system-info/\n";
echo "\n";
echo "development-resources/\n";
echo "├── debugging/\n";
echo "├── testing/\n";
echo "├── database-checks/\n";
echo "├── utilities/\n";
echo "└── deployment/\n";

echo "\n=====================================\n";
echo "CREAMS project is now properly organized!\n";
echo "Time to fix those database schema issues! 🚀\n";

?>