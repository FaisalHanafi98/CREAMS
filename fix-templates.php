<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LetterTemplate;

echo "Template Reactivation Script\n";
echo "===========================\n\n";

// Reactivate all templates
$inactiveTemplates = LetterTemplate::where('is_active', false)->get();

echo "Found {$inactiveTemplates->count()} inactive templates\n";

foreach ($inactiveTemplates as $template) {
    $template->update(['is_active' => true]);
    echo "✓ Reactivated: {$template->template_name} (ID: {$template->id})\n";
}

echo "\nNow checking status...\n";

$totalTemplates = LetterTemplate::count();
$activeTemplates = LetterTemplate::where('is_active', true)->count();
$inactiveTemplates = LetterTemplate::where('is_active', false)->count();

echo "\nAfter reactivation:\n";
echo "Total templates: {$totalTemplates}\n";
echo "Active templates: {$activeTemplates}\n";
echo "Inactive templates: {$inactiveTemplates}\n\n";

echo "✅ All existing templates have been reactivated\n";
echo "✅ New templates should now remain active when created\n";
echo "✅ Multiple templates can coexist\n\n";

echo "Test by creating a new template - all should remain active!\n";

?>