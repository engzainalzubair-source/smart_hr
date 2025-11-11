<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Performance;
use App\Models\RewardPenalty;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HRDemoSeeder extends Seeder
{
    public function run()
    {
        // Keep idempotent: do not duplicate if many rows exist
        if (Employee::count() > 20) {
            $this->command->info('HRDemoSeeder: sample data looks present, skipping.');
            return;
        }

        $faker = \Faker\Factory::create();

        // Create sample employees
        $employees = [];
        for ($i = 1; $i <= 25; $i++) {
            $first = $faker->firstName;
            $last = $faker->lastName;
            $email = strtolower($first . '.' . $last) . $i . '@example.com';
            $hire = Carbon::now()->subDays(rand(10, 900));

            $emp = Employee::create([
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'phone' => $faker->phoneNumber,
                'hire_date' => $hire->toDateString(),
                'salary' => rand(2000, 8000),
                'status' => 'active',
            ]);

            $employees[] = $emp;
        }

        // Attendance: last 14 days for a subset
        $days = 14;
        foreach ($employees as $emp) {
            for ($d = 0; $d < $days; $d++) {
                $date = Carbon::now()->subDays($d)->toDateString();
                // Random presence pattern
                $rnd = rand(1, 100);
                if ($rnd <= 5) {
                    $status = 'absent';
                    $checkIn = null;
                    $checkOut = null;
                } elseif ($rnd <= 15) {
                    $status = 'late';
                    $checkIn = '09:' . str_pad(rand(10, 50), 2, '0', STR_PAD_LEFT);
                    $checkOut = '17:' . str_pad(rand(0, 50), 2, '0', STR_PAD_LEFT);
                } else {
                    $status = 'present';
                    $checkIn = '08:' . str_pad(rand(0, 50), 2, '0', STR_PAD_LEFT);
                    $checkOut = '17:' . str_pad(rand(0, 50), 2, '0', STR_PAD_LEFT);
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

        // Performance: weekly scores for last 6 weeks for random employees
        for ($w = 0; $w < 6; $w++) {
            $start = Carbon::now()->subWeeks($w)->startOfWeek()->toDateString();
            $end = Carbon::now()->subWeeks($w)->endOfWeek()->toDateString();
            foreach (array_slice($employees, 0, 12) as $emp) {
                Performance::create([
                    'employee_id' => $emp->id,
                    'period_start' => $start,
                    'period_end' => $end,
                    'score' => rand(60, 95),
                    'notes' => $faker->sentence,
                ]);
            }
        }

        // Rewards / Penalties: random items
        foreach (array_slice($employees, 0, 10) as $emp) {
            $count = rand(0, 3);
            for ($k = 0; $k < $count; $k++) {
                RewardPenalty::create([
                    'employee_id' => $emp->id,
                    'type' => (rand(0,1) ? 'reward' : 'penalty'),
                    'amount' => rand(50, 1500),
                    'reason' => $faker->sentence,
                    'issued_at' => Carbon::now()->subDays(rand(1, 120))->toDateString(),
                ]);
            }
        }

        $this->command->info('HRDemoSeeder: created sample employees, attendances, performances, and rewards/penalties.');
    }
}
