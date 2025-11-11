<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user if not exists (avoid unique constraint on repeated seeds)
        \App\Models\User::firstOrCreate([
            'email' => 'test@example.com'
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password')
        ]);

        // Seed HR comprehensive dataset (destructive - will wipe HR demo data)
        if (app()->environment('local')) {
            // ensure the seeder class file is loaded (helps when composer autoload/cache is out-of-sync)
            if (! class_exists(\Database\Seeders\HRComprehensiveSeeder::class)) {
                require_once base_path('database/seeders/HRComprehensiveSeeder.php');
            }
            $this->call(\Database\Seeders\HRComprehensiveSeeder::class);
        }
    }
}
