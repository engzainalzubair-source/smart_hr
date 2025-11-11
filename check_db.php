<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Departments: ' . \App\Models\Department::count() . PHP_EOL;
echo 'Employees: ' . \App\Models\Employee::count() . PHP_EOL;
echo 'Attendances: ' . \App\Models\Attendance::count() . PHP_EOL;
echo 'Performances: ' . \App\Models\Performance::count() . PHP_EOL;
echo 'Rewards: ' . \App\Models\RewardPenalty::count() . PHP_EOL;
