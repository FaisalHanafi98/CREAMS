<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LetterTemplate;

echo "Template Status Check\n";
echo "====================\n\n";

$totalTemplates = LetterTemplate::count();
$activeTemplates = LetterTemplate::where('is_active', true)->count();
$inactiveTemplates = LetterTemplate::where('is_active', false)->count();

echo "Total templates: {$totalTemplates}\n";
echo "Active templates: {$activeTemplates}\n";
echo "Inactive templates: {$inactiveTemplates}\n\n";

echo "Template Details:\n";
echo "-----------------\n";

$templates = LetterTemplate::orderBy('created_at', 'desc')->get();

foreach ($templates as $template) {
    $status = $template->is_active ? 'ACTIVE' : 'INACTIVE';
    $createdBy = $template->created_by ?? 'Unknown';
    echo "ID: {$template->id} | {$status} | Name: {$template->template_name} | Created: {$template->created_at} | By: {$createdBy}\n";
}

if ($totalTemplates === 0) {
    echo "\nNo templates found in database!\n";
} elseif ($activeTemplates === 1 && $totalTemplates > 1) {
    echo "\nISSUE DETECTED: Only 1 active template but {$totalTemplates} total templates exist.\n";
    echo "This suggests templates are still being deactivated when new ones are created.\n";
}

?>