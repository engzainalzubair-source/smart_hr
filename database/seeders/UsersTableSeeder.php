<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Create roles (requires spatie/laravel-permission)
        if (class_exists(Role::class)) {
            Role::firstOrCreate(['name' => 'admin']);
            Role::firstOrCreate(['name' => 'hr_manager']);
            Role::firstOrCreate(['name' => 'finance']);
            Role::firstOrCreate(['name' => 'employee']);
        }

        // Create a sample employee and user
        $employee = Employee::firstOrCreate([
            'first_name' => 'Ahmed',
            'last_name' => 'Yahya',
            'email' => 'ahmed@example.com'
        ]);

        $user = User::firstOrCreate([
            'email' => 'admin@example.com'
        ], [
            'name' => 'Admin',
            'password' => bcrypt('password')
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('admin');
        }
    }
}
