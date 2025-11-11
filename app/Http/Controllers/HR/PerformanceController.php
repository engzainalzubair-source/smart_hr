<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Performance;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Services\PerformanceRecommendationService;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $departmentId = $request->query('department_id');

        $performances = Performance::with('employee')
            ->when($q, function($qry) use ($q) {
                $qry->whereHas('employee', function($e) use ($q) {
                    $e->where('first_name', 'like', "%{$q}%")
                      ->orWhere('last_name', 'like', "%{$q}%")
                      ->orWhereRaw("concat(first_name,' ',last_name) like ?", ["%{$q}%"]);
                });
            })
            ->when($departmentId, function($qry) use ($departmentId) {
                $qry->whereHas('employee', function($e) use ($departmentId) {
                    $e->where('department_id', $departmentId);
                });
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        // load departments for filter
        $departments = Department::orderBy('name')->get();

        // Composite scoring: combine recent performance and attendance
        $days = 90;
        $start = now()->subDays($days - 1)->startOfDay();

        $employeesQuery = Employee::where('status','active')
            ->when($departmentId, function($qry) use ($departmentId){
                $qry->where('department_id', $departmentId);
            })
            ->when($q, function($qry) use ($q){
                $qry->whereRaw("concat(first_name,' ',last_name) like ?", ["%{$q}%"]);
            });

        $employees = $employeesQuery->get();

        $composites = [];
        foreach ($employees as $emp) {
            $perfAvg = Performance::where('employee_id', $emp->id)
                ->where('period_start', '>=', $start)
                ->avg('score') ?? 0;
            $presentCount = \App\Models\Attendance::where('employee_id', $emp->id)
                ->where('date', '>=', $start->toDateString())
                ->where('status','present')
                ->count();
            // attendance rate over the window (approx)
            $attendanceRate = ($days > 0) ? ($presentCount / $days) * 100 : 0;
            // weights: performance 70%, attendance 30%
            $composite = (0.7 * $perfAvg) + (0.3 * $attendanceRate);
            $composites[] = [
                'employee_id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'perfAvg' => round($perfAvg,1),
                'attendanceRate' => round($attendanceRate,1),
                'composite' => round($composite,1),
            ];
        }
        // sort by composite desc and pick top 20 for chart/table
        usort($composites, function($a,$b){ return $b['composite'] <=> $a['composite']; });
        $topComposites = array_slice($composites, 0, 20);

        // generate recommendations for sidebar (light-weight, explainable rules)
        try {
            $svc = app()->make(PerformanceRecommendationService::class);
            $recommendations = $svc->generate($topComposites);
        } catch (\Throwable $e) {
            $recommendations = [];
        }

        // Build a lookup by employee_id for quick access in the table view
        $recommendationsById = [];
        foreach ($recommendations as $r) {
            if (!empty($r['employee_id'])) {
                $recommendationsById[$r['employee_id']] = $r;
            }
        }

        if ($request->ajax()) {
            return view('hr.partials.performances_table', compact('performances','topComposites','departments','recommendations','recommendationsById'));
        }
        return view('hr.performances.index', compact('performances','topComposites','departments','recommendations','recommendationsById'));
    }

    /**
     * Export the current filtered report as PDF.
     * Requires barryvdh/laravel-dompdf (composer require barryvdh/laravel-dompdf)
     */
    public function exportPdf(Request $request)
    {
        $q = $request->query('q');
        $departmentId = $request->query('department_id');

        // Reuse same composite computation logic for the filtered set
        $days = 90;
        $start = now()->subDays($days - 1)->startOfDay();

        $employeesQuery = Employee::where('status','active')
            ->when($departmentId, function($qry) use ($departmentId){
                $qry->where('department_id', $departmentId);
            })
            ->when($q, function($qry) use ($q){
                $qry->whereRaw("concat(first_name,' ',last_name) like ?", ["%{$q}%"]);
            });

        $employees = $employeesQuery->get();

        $composites = [];
        foreach ($employees as $emp) {
            $perfAvg = Performance::where('employee_id', $emp->id)
                ->where('period_start', '>=', $start)
                ->avg('score') ?? 0;
            $presentCount = \App\Models\Attendance::where('employee_id', $emp->id)
                ->where('date', '>=', $start->toDateString())
                ->where('status','present')
                ->count();
            $attendanceRate = ($days > 0) ? ($presentCount / $days) * 100 : 0;
            $composite = (0.7 * $perfAvg) + (0.3 * $attendanceRate);
            $composites[] = [
                'employee_id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'perfAvg' => round($perfAvg,1),
                'attendanceRate' => round($attendanceRate,1),
                'composite' => round($composite,1),
            ];
        }
        usort($composites, function($a,$b){ return $b['composite'] <=> $a['composite']; });
        $topComposites = array_slice($composites, 0, 1000);

        $filters = ['q' => $q, 'department_id' => $departmentId];

        // Render PDF using a dedicated blade view
        // generate lightweight recommendations to include in the PDF as well
        try {
            $svc = app()->make(PerformanceRecommendationService::class);
            $recommendations = $svc->generate($topComposites);
        } catch (\Throwable $e) {
            $recommendations = [];
        }

        $pdf = PDF::loadView('hr.reports.performance_pdf', compact('topComposites','filters','recommendations'))
            ->setPaper('a4', 'landscape');

    // stream() sends inline content so browser can open in new tab instead of forcing download
    return $pdf->stream('performance_report.pdf');
    }

    public function create()
    {
        $employees = Employee::orderBy('first_name')->get();
        return view('hr.performances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'score' => 'required|numeric',
        ]);
        Performance::create($data);
        return redirect()->route('hr.performances.index');
    }

    public function edit(Performance $performance)
    {
        $employees = Employee::orderBy('first_name')->get();
        return view('hr.performances.edit', compact('performance','employees'));
    }

    public function update(Request $request, Performance $performance)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'score' => 'required|numeric',
        ]);
        $performance->update($data);
        return redirect()->route('hr.performances.index');
    }

    public function destroy(Performance $performance)
    {
        $performance->delete();
        return redirect()->route('hr.performances.index');
    }
}
