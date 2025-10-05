<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Trainee;

$deleted = Trainee::where('trainee_id', 'LIKE', 'TEST-%')->delete();
echo "Cleaned up {$deleted} test trainees" . PHP_EOL;