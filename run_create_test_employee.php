<?php
// Creates a test employee record and a matching user for local testing.
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (! config('app.debug')) {
    echo "Refusing to run: APP_DEBUG is false. This script must be run in local/dev only.\n";
    exit(1);
}

use Illuminate\Support\Facades\Hash;

$email = 'employee@example.com';
$password = env('EMP_DEFAULT_PASSWORD', 'Emp@12345');

// Create Employee record
$employeeClass = \App\Models\Employee::class;
$existingEmp = $employeeClass::where('email', $email)->first();
if (! $existingEmp) {
    $emp = $employeeClass::create([
        'department_id' => null,
        'first_name' => 'Test',
        'last_name' => 'Employee',
        'email' => $email,
        'phone' => '+201000000000',
        'address' => 'Test Address',
        'job_title' => 'Tester',
        'hire_date' => now()->toDateString(),
        'salary' => 0,
        'status' => 'active',
    ]);
    echo "Created Employee record (id={$emp->id}) with email {$email}\n";
} else {
    $emp = $existingEmp;
    echo "Employee record already exists (id={$emp->id})\n";
}

// Create User
$userClass = \App\Models\User::class;
$existingUser = $userClass::where('email', $email)->first();
if (! $existingUser) {
    $user = $userClass::create([
        'name' => $emp->first_name . ' ' . $emp->last_name,
        'email' => $email,
        'password' => Hash::make($password),
    ]);
    echo "Created user: {$email} with password: {$password}\n";
} else {
    $user = $existingUser;
    echo "User already exists (id={$user->id})\n";
}

echo "Done. Login credentials:\n";
echo "  email: {$email}\n";
echo "  password: {$password}\n";
