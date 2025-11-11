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

class HRComprehensiveSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('HRComprehensiveSeeder: wiping and re-seeding HR tables...');

        // Disable FK checks for truncation in a driver-safe way
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        // Truncate in order
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

        $faker = \Faker\Factory::create();

        // Create departments (representative list)
        $departments = [
            ['name' => 'الموارد البشرية', 'description' => 'إدارة شؤون الموظفين'],
            ['name' => 'تكنولوجيا المعلومات', 'description' => 'تطوير ودعم الأنظمة'],
            ['name' => 'المبيعات', 'description' => 'فرق المبيعات والتسويق'],
            ['name' => 'الخدمات المالية', 'description' => 'المحاسبة والمالية'],
            ['name' => 'العمليات', 'description' => 'إدارة العمليات اليومية'],
            ['name' => 'خدمة العملاء', 'description' => 'دعم العملاء ورضاهم'],
            ['name' => 'الإدارة', 'description' => 'الإدارة العليا والاستراتيجية'],
            ['name' => 'البحث والتطوير', 'description' => 'ابتكار وتحسين المنتجات'],
        ];

        foreach ($departments as $d) {
            Department::create($d);
        }

        $deps = Department::all();

        // Create employees across departments with varied scenarios
        $employees = [];
    for ($i = 0; $i < 30; $i++) {
            $first = $faker->firstName;
            $last = $faker->lastName;
            $hire = Carbon::now()->subDays(rand(5, 2000));
            $dep = $deps->random();
            $status = (rand(1,100) <= 8) ? 'archived' : 'active';
            $salary = rand(2000, 12000);

            $emp = Employee::create([
                'department_id' => $dep->id,
                'first_name' => $first,
                'last_name' => $last,
                'email' => strtolower($first . '.' . $last . $i) . '@example.com',
                'phone' => $faker->phoneNumber,
                'hire_date' => $hire->toDateString(),
                'job_title' => $faker->jobTitle,
                'salary' => $salary,
                'status' => $status,
            ]);

            // Soft-delete some employees to exercise archive behavior
            if ($i % 23 === 0) {
                $emp->delete();
            }

            $employees[] = $emp;
        }

        $this->command->info('HRComprehensiveSeeder: created ' . count($employees) . ' employees');

        // Attendances: simulate last 120 days with realistic patterns
    // reduce days for faster local seeding; increase if you want larger datasets
    $days = 60;
        foreach ($employees as $emp) {
            // give some employees exceptionally low attendance or none
            $attendanceProbability = in_array($emp->status, ['archived']) ? 40 : 85;
            for ($d = 0; $d < $days; $d++) {
                $date = Carbon::now()->subDays($d)->toDateString();
                $rnd = rand(1,100);
                if ($rnd > $attendanceProbability) {
                    $status = 'absent';
                    $checkIn = null; $checkOut = null;
                } elseif ($rnd <= 10) {
                    $status = 'late';
                    $checkIn = '09:' . str_pad(rand(5,59),2,'0',STR_PAD_LEFT);
                    $checkOut = '17:' . str_pad(rand(0,59),2,'0',STR_PAD_LEFT);
                } else {
                    $status = 'present';
                    $checkIn = '08:' . str_pad(rand(0,59),2,'0',STR_PAD_LEFT);
                    $checkOut = '17:' . str_pad(rand(0,59),2,'0',STR_PAD_LEFT);
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

        $this->command->info('HRComprehensiveSeeder: created attendances');

        // Performances: weekly scores for last 52 weeks for most employees
    // reduce weeks for faster local seeding
    for ($w = 0; $w < 12; $w++) {
            $start = Carbon::now()->subWeeks($w)->startOfWeek()->toDateString();
            $end = Carbon::now()->subWeeks($w)->endOfWeek()->toDateString();
            foreach ($employees as $emp) {
                // vary score based on department and randomness
                $base = 70 + ($emp->department_id % 6) * 3; // some dept bias
                $score = max(35, min(100, round($base + rand(-20,20) + rand(-5,5)/10,1)));
                Performance::create([
                    'employee_id' => $emp->id,
                    'period_start' => $start,
                    'period_end' => $end,
                    'score' => $score,
                    'notes' => $faker->sentence,
                ]);
            }
        }

    $this->command->info('HRComprehensiveSeeder: created performances');

        // Rewards & Penalties: create mixed records
        foreach ($employees as $emp) {
            $count = rand(0,4);
            for ($k = 0; $k < $count; $k++) {
                RewardPenalty::create([
                    'employee_id' => $emp->id,
                    'type' => rand(0,1) ? 'reward' : 'penalty',
                    'amount' => rand(20, 2000),
                    'reason' => $faker->sentence,
                    'issued_at' => Carbon::now()->subDays(rand(0, 365))->toDateString(),
                ]);
            }
        }

        $this->command->info('HRComprehensiveSeeder: finished. Departments: ' . Department::count() . ', Employees: ' . Employee::count());
    }
}
