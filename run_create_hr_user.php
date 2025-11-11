<?php
// One-off script to create an 'hr' role and a user named 'hr' (local/dev only)
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (! config('app.debug')) {
    echo "Refusing to run: APP_DEBUG is false. This script must be run in local/dev only.\n";
    exit(1);
}

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

// Create role and user
$roleName = 'hr';
$email = 'hr@example.com';
$password = env('HR_DEFAULT_PASSWORD', 'Hr@12345');

// Load Spatie Role model if available
if (class_exists(\Spatie\Permission\Models\Role::class)) {
    $roleModel = \Spatie\Permission\Models\Role::class;
    if (! $roleModel::where('name', $roleName)->exists()) {
        $roleModel::create(['name' => $roleName]);
        echo "Created role: {$roleName}\n";
    } else {
        echo "Role '{$roleName}' already exists.\n";
    }
} else {
    echo "Spatie Role model not found. Ensure spatie/laravel-permission is installed.\n";
}

// Create user if not exists
if (class_exists(\App\Models\User::class)) {
    $userClass = \App\Models\User::class;
    $user = $userClass::where('email', $email)->first();
    if (! $user) {
        $user = $userClass::create([
            'name' => 'hr',
            'email' => $email,
            'password' => Hash::make($password),
        ]);
        echo "Created user: {$email} with password: {$password}\n";
    } else {
        echo "User with email {$email} already exists (id={$user->id}).\n";
    }

    // assign role
    if (method_exists($user, 'assignRole')) {
        $user->assignRole($roleName);
        echo "Assigned role '{$roleName}' to user {$email}.\n";
    } else {
        echo "User model does not support assignRole(). Ensure HasRoles trait is present.\n";
    }
} else {
    echo "User model not found.\n";
}

echo "Done. You can now login as '{$email}'.\n";
