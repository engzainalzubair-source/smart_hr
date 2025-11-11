<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Performance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $startWindow = Carbon::now()->subDays(90)->startOfDay();

        // load paginated departments with employees eager-loaded to compute per-department stats
        $departments = Department::with('employees')->orderBy('name')->paginate(20)->appends($request->query());

        // also load all departments once for overall analytics (small number expected)
        $allDepartments = Department::with('employees')->orderBy('name')->get();

        $totalDepartments = $allDepartments->count();
        $totalEmployees = Employee::count();
        $totalPayroll = (float) Employee::sum('salary');
        $avgSalaryOverall = $totalEmployees ? (float) Employee::avg('salary') : 0.0;

        // compute per-department stats for the current page
        $deptStats = [];
        foreach ($departments->items() as $dept) {
            $emps = $dept->employees;
            $empIds = $emps->pluck('id')->toArray();
            $salaries = $emps->pluck('salary')->filter(function($v){ return $v !== null; })->map(function($v){ return (float)$v; })->values();
            $totalSalary = $salaries->sum();
            $avgSalary = $salaries->count() ? $salaries->avg() : 0;
            $maxSalary = $salaries->count() ? $salaries->max() : 0;
            $minSalary = $salaries->count() ? $salaries->min() : 0;

            $avgPerf = 0;
            if (!empty($empIds)) {
                $avgPerf = (float) Performance::whereIn('employee_id', $empIds)
                    ->where('period_start', '>=', $startWindow)
                    ->avg('score') ?? 0.0;
            }

            $deptStats[$dept->id] = [
                'employees_count' => $emps->count(),
                'total_salary' => $totalSalary,
                'avg_salary' => round($avgSalary, 2),
                'max_salary' => $maxSalary,
                'min_salary' => $minSalary,
                'avg_performance' => round($avgPerf, 1),
            ];
        }

        // overall analytics: top departments by average performance (last 90 days) and by payroll
        $deptPerfList = [];
        $deptPayrollList = [];
        foreach ($allDepartments as $d) {
            $emps = $d->employees;
            $empIds = $emps->pluck('id')->toArray();
            $payroll = (float) $emps->pluck('salary')->filter()->sum();
            $avgPerf = 0;
            if (!empty($empIds)) {
                $avgPerf = (float) Performance::whereIn('employee_id', $empIds)
                    ->where('period_start', '>=', $startWindow)
                    ->avg('score') ?? 0.0;
            }
            $deptPerfList[] = ['id' => $d->id, 'name' => $d->name, 'avg_perf' => round($avgPerf,1)];
            $deptPayrollList[] = ['id' => $d->id, 'name' => $d->name, 'payroll' => $payroll];
        }
        usort($deptPerfList, function($a,$b){ return $b['avg_perf'] <=> $a['avg_perf']; });
        usort($deptPayrollList, function($a,$b){ return $b['payroll'] <=> $a['payroll']; });

        $topByPerf = array_slice($deptPerfList, 0, 3);
        $topByPayroll = array_slice($deptPayrollList, 0, 3);

        return view('hr.departments.index', compact(
            'departments','deptStats','totalDepartments','totalEmployees','totalPayroll','avgSalaryOverall','topByPerf','topByPayroll'
        ));
    }

    /**
     * Export departments table as PDF (table only)
     */
    public function exportPdf(Request $request)
    {
        $startWindow = Carbon::now()->subDays(90)->startOfDay();
        $allDepartments = Department::with('employees')->orderBy('name')->get();

        $deptStats = [];
        foreach ($allDepartments as $d) {
            $emps = $d->employees;
            $empIds = $emps->pluck('id')->toArray();
            $salaries = $emps->pluck('salary')->filter(function($v){ return $v !== null; })->map(function($v){ return (float)$v; })->values();
            $totalSalary = $salaries->sum();
            $avgSalary = $salaries->count() ? $salaries->avg() : 0;
            $maxSalary = $salaries->count() ? $salaries->max() : 0;
            $minSalary = $salaries->count() ? $salaries->min() : 0;

            $avgPerf = 0;
            if (!empty($empIds)) {
                $avgPerf = (float) Performance::whereIn('employee_id', $empIds)
                    ->where('period_start', '>=', $startWindow)
                    ->avg('score') ?? 0.0;
            }

            $deptStats[$d->id] = [
                'employees_count' => $emps->count(),
                'total_salary' => $totalSalary,
                'avg_salary' => round($avgSalary, 2),
                'max_salary' => $maxSalary,
                'min_salary' => $minSalary,
                'avg_performance' => round($avgPerf, 1),
            ];
        }

        $pdf = PDF::loadView('hr.departments.pdf', ['departments' => $allDepartments, 'deptStats' => $deptStats])
            ->setPaper('a4', 'landscape');

        return $pdf->download('departments_table.pdf');
    }

    public function create()
    {
        return view('hr.departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:departments,name',
            'description' => 'nullable|string',
        ]);
        Department::create($data);
        return redirect()->route('hr.departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return view('hr.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
        ]);
        $department->update($data);
        return redirect()->route('hr.departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        // optional: prevent deletion if employees exist. For now allow delete.
        $department->delete();
        return redirect()->route('hr.departments.index')->with('success', 'Department deleted successfully.');
    }
}
