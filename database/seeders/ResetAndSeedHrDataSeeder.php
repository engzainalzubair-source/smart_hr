<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Performance;
use App\Models\RewardPenalty;
use Carbon\Carbon;

class ResetAndSeedHrDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('ResetAndSeedHrDataSeeder: wiping HR tables (preserving users) and seeding fresh data...');

        // Disable FK checks
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        // Truncate HR-related tables only (do not truncate users)
        $this->command->info('Truncating HR tables...');
        RewardPenalty::truncate();
        Performance::truncate();
        Attendance::truncate();
        Employee::truncate();
        Department::truncate();

        // Re-enable FK checks
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $faker = \Faker\Factory::create('en_US'); // use english locale for job titles etc.

        // 10 English department names
        $departments = [
            ['name' => 'Human Resources', 'description' => 'Employee relations and HR processes'],
            ['name' => 'Information Technology', 'description' => 'Systems, infrastructure and development'],
            ['name' => 'Sales', 'description' => 'Sales and business development'],
            ['name' => 'Finance', 'description' => 'Accounting and financial operations'],
            ['name' => 'Operations', 'description' => 'Daily operations management'],
            ['name' => 'Customer Service', 'description' => 'Customer support and satisfaction'],
            ['name' => 'Executive', 'description' => 'Executive management and strategy'],
            ['name' => 'Research & Development', 'description' => 'Product R&D and innovation'],
            ['name' => 'Legal', 'description' => 'Legal and compliance'],
            ['name' => 'Marketing', 'description' => 'Marketing and communications'],
        ];

        foreach ($departments as $d) {
            Department::create($d);
        }

        $deps = Department::all();

        // Saudi-style first and last names (English transliteration)
        $firstNames = [
            'Mohammed','Abdullah','Fahad','Yusuf','Saad','Turki','Nasser','Hamad','Khalid','Saleh',
            'Riyadh','Sultan','Ammar','Bader','Hussain','Talal','Ibrahim','Zaid','Majed','Omar'
        ];
        $lastNames = [
            'Al Saud','Al-Harbi','Al-Qahtani','Al-Otaibi','Al-Shammari','Al-Anazi','Al-Mutairi',
            'Al-Faisal','Al-Ghamdi','Al-Jaber','Al-Rashid','Al-Hussain','Al-Zahrani','Al-Shehri',
            'Al-Ruwais','Al-Sabti','Al-Sulami','Al-Nasser','Al-Bassam','Al-Mansour'
        ];

        $this->command->info('Creating Saudi employees (10 per department)...');

        $employees = [];
        foreach ($deps as $dep) {
            for ($i = 0; $i < 10; $i++) {
                $first = $firstNames[array_rand($firstNames)];
                $last = $lastNames[array_rand($lastNames)];
                // ensure unique-ish email
                $local = strtolower(preg_replace('/[^a-z]/', '', $first)) . '.' . strtolower(preg_replace('/[^a-z]/', '', $last)) . rand(1,999);
                $email = $local . '@example.sa';
                $hire = Carbon::now()->subDays(rand(30, 2000));
                $salary = rand(5000, 25000); // SAR range
                $statusRand = rand(1,100);
                $status = $statusRand <= 5 ? 'archived' : ($statusRand <= 10 ? 'on_leave' : 'active');

                $job = $faker->jobTitle;

                $phone = '+9665' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

                $emp = Employee::create([
                    'department_id' => $dep->id,
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => $email,
                    'phone' => $phone,
                    'hire_date' => $hire->toDateString(),
                    'job_title' => $job,
                    'salary' => $salary,
                    'status' => $status,
                ]);

                // occasionally soft-delete (archive) to exercise archived behavior
                if ($status === 'archived' && rand(1,3) === 1) {
                    $emp->delete();
                }

                $employees[] = $emp;
            }
        }

        $this->command->info('Created ' . count($employees) . ' employees.');

        // Create attendances for last 60 days
        $days = 60;
        $this->command->info("Generating attendance for last {$days} days...");
        foreach ($employees as $emp) {
            // variation: some employees have lower attendance probability
            $attendanceProbability = ($emp->status === 'archived') ? 40 : 88;
            for ($d = 0; $d < $days; $d++) {
                $date = Carbon::now()->subDays($d)->toDateString();
                $rnd = rand(1, 100);
                if ($rnd > $attendanceProbability) {
                    $status = 'absent';
                    $checkIn = null; $checkOut = null;
                } elseif ($rnd <= 8) {
                    $status = 'late';
                    $checkIn = sprintf('%02d:%02d', 9, rand(5,59));
                    $checkOut = sprintf('%02d:%02d', 17, rand(0,59));
                } else {
                    $status = 'present';
                    $checkIn = sprintf('%02d:%02d', 8, rand(0,59));
                    $checkOut = sprintf('%02d:%02d', 17, rand(0,59));
                }

                Attendance::create([
                    'employee_id' => $emp->id,
                    'date' => $date,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'status' => $status,
                ]);
            }
        }

        $this->command->info('Attendances seeded.');

        // Create weekly performance records for last 12 weeks
        $weeks = 12;
        $this->command->info("Generating performance for last {$weeks} weeks...");
        for ($w = 0; $w < $weeks; $w++) {
            $start = Carbon::now()->subWeeks($w)->startOfWeek()->toDateString();
            $end = Carbon::now()->subWeeks($w)->endOfWeek()->toDateString();
            foreach ($employees as $emp) {
                // introduce department bias and randomness
                $base = 70 + ($emp->department_id % 5) * 2;
                $score = max(35, min(100, round($base + rand(-25,25) + rand(-4,4)/10, 1)));
                Performance::create([
                    'employee_id' => $emp->id,
                    'period_start' => $start,
                    'period_end' => $end,
                    'score' => $score,
                    'notes' => $faker->sentence,
                ]);
            }
        }

        $this->command->info('Performances seeded.');

        // Rewards & Penalties - random
        $this->command->info('Seeding rewards and penalties...');
        foreach ($employees as $emp) {
            $count = rand(0,3);
            for ($k = 0; $k < $count; $k++) {
                RewardPenalty::create([
                    'employee_id' => $emp->id,
                    'type' => rand(0,1) ? 'reward' : 'penalty',
                    'amount' => rand(50, 5000),
                    'reason' => $faker->sentence,
                    'issued_at' => Carbon::now()->subDays(rand(0, 365))->toDateString(),
                ]);
            }
        }

        $this->command->info('ResetAndSeedHrDataSeeder: finished. Departments: ' . Department::count() . ', Employees: ' . Employee::count());
    }
}
