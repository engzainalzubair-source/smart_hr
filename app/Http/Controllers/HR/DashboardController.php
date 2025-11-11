<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Performance;
use App\Models\Attendance;
use App\Models\RewardPenalty;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees = Employee::count();
        $newEmployees = Employee::where('hire_date', '>=', Carbon::now()->subMonth())->count();
        $monthlyPerformance = Performance::whereBetween('period_start', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->avg('score') ?? 0;

        // Additional KPIs
        $activeEmployees = Employee::where('status', 'active')->count();
        $presentToday = Attendance::whereDate('date', Carbon::now()->toDateString())->where('status','present')->count();
        $lateToday = Attendance::whereDate('date', Carbon::now()->toDateString())->where('status','late')->count();
    $totalPayroll = Employee::sum('salary');
    $totalDepartments = Department::count();
        $onLeave = Attendance::whereDate('date', Carbon::now()->toDateString())->where('status','on_leave')->count();

    // Archived employees (soft-deleted)
    $archivedEmployees = Employee::onlyTrashed()->count();

    // Attendance % (present / total) guard against division by zero
    $attendancePercentage = $totalEmployees ? round(($presentToday / $totalEmployees) * 100, 1) : 0;

    // 30-day average performance
    $avgPerformance30d = Performance::where('period_start', '>=', Carbon::now()->subDays(30))->avg('score') ?? 0;

        // pending requests (safe)
        $pendingRequests = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('rewards_penalties')) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('rewards_penalties', 'processed_at')) {
                $pendingRequests = RewardPenalty::whereNull('processed_at')->count();
            } else {
                $pendingRequests = RewardPenalty::count();
            }
        }

        // Simple chart data (last 4 weeks average performance)
        $chartData = [];
        // produce human-friendly labels like "01 Nov - 07 Nov"
        for ($i = 3; $i >= 0; $i--) {
            $start = Carbon::now()->subWeeks($i)->startOfWeek();
            $end = Carbon::now()->subWeeks($i)->endOfWeek();
            $label = $start->format('d M') . ' - ' . $end->format('d M');
            $avg = Performance::whereBetween('period_start', [$start, $end])->avg('score') ?? 0;
            $chartData[$label] = (float) round($avg, 1);
        }

        // Fallback demo data for weekly chart if there's no real data yet
        $chartVals = array_values($chartData);
        if (count($chartVals) === 0 || array_sum($chartVals) == 0) {
            // gentle realistic demo values
            $demoWeekVals = [72.0, 75.5, 80.2, 78.0];
            $i = 0;
            foreach (array_keys($chartData) as $k) {
                $chartData[$k] = $demoWeekVals[$i] ?? 70.0;
                $i++;
            }
            // if keys are missing (edge-case), regenerate labels with demo keys
            if (empty($chartData)) {
                $labelsDemo = [];
                for ($i = 3; $i >= 0; $i--) {
                    $s = Carbon::now()->subWeeks($i)->startOfWeek();
                    $e = Carbon::now()->subWeeks($i)->endOfWeek();
                    $labelsDemo[] = $s->format('d M') . ' - ' . $e->format('d M');
                }
                $chartData = array_combine($labelsDemo, $demoWeekVals);
            }
        }

        // Average performance (overall)
        $avgPerformance = Performance::avg('score') ?? 0;

        // 30-day performance series (per-day average)
        $start30 = Carbon::now()->subDays(29)->startOfDay();
        // prepare labels: last 30 days
        $labels30 = [];
        for ($i = 29; $i >= 0; $i--) {
            $labels30[] = Carbon::now()->subDays($i)->toDateString();
        }
        $raw30 = Performance::select(DB::raw("date(period_start) as day"), DB::raw("AVG(score) as avg_score"))
            ->where('period_start', '>=', $start30)
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('avg_score', 'day')
            ->toArray();

        $values30 = array_map(function ($d) use ($raw30) {
            return isset($raw30[$d]) ? (float) round($raw30[$d], 1) : 0.0;
        }, $labels30);

        // top performer in last 30 days (by avg score)
        $topPerformer = Performance::select('employee_id', DB::raw('AVG(score) as avg_score'))
            ->where('period_start', '>=', $start30)
            ->groupBy('employee_id')
            ->orderByDesc('avg_score')
            ->first();
        $topPerformerName = null;
        $topPerformerAvg = null;
        if ($topPerformer) {
            $emp = Employee::find($topPerformer->employee_id);
            $topPerformerName = $emp ? ($emp->first_name . ' ' . $emp->last_name) : null;
            $topPerformerAvg = (float) round($topPerformer->avg_score, 1);
        }

        // 7-day rolling average computed from values30
        $rolling7 = [];
        $n = count($values30);
        for ($i = 0; $i < $n; $i++) {
            $start = max(0, $i - 6);
            $slice = array_slice($values30, $start, $i - $start + 1);
            $cnt = count($slice);
            $rolling7[] = $cnt ? (float) round(array_sum($slice) / $cnt, 1) : 0.0;
        }

        // Fallback demo 30-day data if nothing recorded
        if (array_sum($values30) == 0) {
            $values30 = [];
            for ($i = 0; $i < 30; $i++) {
                // simple upward trend from 60 to 85
                $values30[] = (float) round(60 + ($i * (25/29)), 1);
            }
            // recompute rolling7 from demo values
            $rolling7 = [];
            for ($i = 0; $i < 30; $i++) {
                $start = max(0, $i - 6);
                $slice = array_slice($values30, $start, $i - $start + 1);
                $cnt = count($slice);
                $rolling7[] = $cnt ? (float) round(array_sum($slice) / $cnt, 1) : 0.0;
            }
        }

    // Load tab content once and pass to view (no repeated calls from blade)
        $employees = Employee::with('department')->paginate(15);
    $departmentsList = Department::orderBy('name')->get();
        $attendances = Attendance::with('employee')->latest()->paginate(20);
        $today = Carbon::now()->toDateString();
        $todayAttendances = Attendance::whereDate('date', $today)->where('status','present')->get()->keyBy('employee_id');
        $presentEmployeeIds = $todayAttendances->keys()->toArray();
        $presentEmployees = Employee::whereIn('id', $presentEmployeeIds)->with('department')->get()->map(function ($emp) use ($todayAttendances) {
            $emp->today_check_in = optional($todayAttendances->get($emp->id))->check_in;
            return $emp;
        });
        $performances = Performance::with('employee')->latest()->paginate(20);
        $items = RewardPenalty::with('employee')->latest()->paginate(20);

        // Composite scores for performance tab (same logic used in PerformanceController)
        $days = 90;
        $startWindow = Carbon::now()->subDays($days - 1)->startOfDay();
        $activeEmps = Employee::where('status','active')->get();
        $topComposites = [];
        foreach ($activeEmps as $emp) {
            $perfAvg = Performance::where('employee_id', $emp->id)
                ->where('period_start', '>=', $startWindow)
                ->avg('score') ?? 0;
            $presentCount = Attendance::where('employee_id', $emp->id)
                ->where('date', '>=', $startWindow->toDateString())
                ->where('status','present')
                ->count();
            $attendanceRate = ($days > 0) ? ($presentCount / $days) * 100 : 0;
            $composite = (0.7 * $perfAvg) + (0.3 * $attendanceRate);
            $topComposites[] = [
                'employee_id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'perfAvg' => round($perfAvg,1),
                'attendanceRate' => round($attendanceRate,1),
                'composite' => round($composite,1),
            ];
        }
        usort($topComposites, function($a,$b){ return $b['composite'] <=> $a['composite']; });
        $topComposites = array_slice($topComposites, 0, 20);

        // Rewards/Penalties analytics: last 12 months counts by type
        $months = [];
        $now = Carbon::now();
        for ($m = 11; $m >= 0; $m--) {
            $dt = $now->copy()->subMonths($m);
            $months[] = $dt->format('Y-m');
        }
        $rewardData = RewardPenalty::select(DB::raw("strftime('%Y-%m', created_at) as ym"), 'type', DB::raw('count(*) as cnt'))
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->groupBy('ym','type')
            ->get()
            ->groupBy('ym');

        $labelsRewards = array_map(function($y){ $d = Carbon::createFromFormat('Y-m', $y); return $d->format('M Y'); }, $months);
        $rewardsPerMonth = [];
        $penaltiesPerMonth = [];
        foreach ($months as $ym) {
            $group = $rewardData->get($ym) ?? collect();
            $rewardsPerMonth[] = (int) ($group->firstWhere('type','reward')->cnt ?? 0);
            $penaltiesPerMonth[] = (int) ($group->firstWhere('type','penalty')->cnt ?? 0);
        }

        // Top rewarded employees (by rewards count) last 12 months
        $topRewards = RewardPenalty::select('employee_id', DB::raw("SUM(type='reward') as rewards_count"))
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->groupBy('employee_id')
            ->orderByDesc('rewards_count')
            ->take(5)
            ->get();
        $topRewards = $topRewards->map(function($r){
            $emp = Employee::find($r->employee_id);
            return ['name' => $emp ? ($emp->first_name . ' ' . $emp->last_name) : '—', 'count' => (int)$r->rewards_count];
        });

        return view('hr.dashboard', compact(
            'totalEmployees','newEmployees','monthlyPerformance',
            'activeEmployees','presentToday','lateToday','totalPayroll','totalDepartments','onLeave','pendingRequests','chartData',
            'employees','attendances','performances','items','avgPerformance', 'presentEmployees',
            'archivedEmployees','attendancePercentage','avgPerformance30d',
            'labels30','values30', 'topPerformerName','topPerformerAvg','rolling7',
            'labelsRewards','rewardsPerMonth','penaltiesPerMonth','topRewards','topComposites','departmentsList'
        ))->with('todayPresentIds', $presentEmployeeIds);
    }

}
