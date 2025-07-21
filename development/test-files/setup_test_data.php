<?php

require_once "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LetterTemplate;
use App\Models\Letter;

echo "=== SETTING UP TEST DATA ===" . PHP_EOL;

// Create or update an active template
$template = LetterTemplate::where("is_active", true)->first();
if (\!$template) {
    echo "Creating new active template..." . PHP_EOL;
    $template = LetterTemplate::create([
        "template_name" => "Default Letter Template",
        "template_content" => "[CONTENT]",
        "template_type" => "letter",
        "template_variables" => [
            "header_content" => "CREAMS - Community-based Rehabilitation Management System",
            "footer_content" => "This is an official document from CREAMS."
        ],
        "is_active" => true,
        "created_by" => 49
    ]);
    echo "Template created with ID: " . $template->id . PHP_EOL;
} else {
    echo "Active template exists: " . $template->template_name . PHP_EOL;
}

// Clean up any existing letters to start fresh
Letter::truncate();
echo "Cleared existing letters for fresh testing." . PHP_EOL;

echo "Test data setup complete\!" . PHP_EOL;

