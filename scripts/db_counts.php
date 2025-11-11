<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Employees: " . App\Models\Employee::count() . PHP_EOL;
echo "Attendances: " . App\Models\Attendance::count() . PHP_EOL;
echo "Performances: " . App\Models\Performance::count() . PHP_EOL;
echo "Rewards: " . App\Models\RewardPenalty::count() . PHP_EOL;
