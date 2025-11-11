<?php
// One-off script to run HRComprehensiveSeeder directly (boots Laravel app)
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ensure class is loaded
if (! class_exists(\Database\Seeders\HRComprehensiveSeeder::class)) {
    require_once __DIR__ . '/database/seeders/HRComprehensiveSeeder.php';
}

$seeder = new \Database\Seeders\HRComprehensiveSeeder();
// Provide a fake 'command' property so seeder->command->info calls won't fail
if (property_exists($seeder, 'command')) {
    $rp = new ReflectionProperty($seeder, 'command');
    $rp->setAccessible(true);
    $rp->setValue($seeder, new class {
        public function info($msg) { echo "[INFO] $msg\n"; }
    });
}

$seeder->run();
echo "HRComprehensiveSeeder executed.\n";
