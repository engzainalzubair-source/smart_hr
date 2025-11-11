<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RewardPenalty;
use App\Models\Salary;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        // sanitize year/month inputs: enforce reasonable ranges
        $year = (int) $request->query('year', Carbon::now()->year);
        $month = (int) $request->query('month', Carbon::now()->month);
        $now = Carbon::now();
        if ($year < 1970 || $year > $now->year + 1) {
            $year = $now->year;
        }
        if ($month < 1 || $month > 12) {
            $month = $now->month;
        }

        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = (clone $start)->endOfMonth()->endOfDay();

        $employees = Employee::whereNull('deleted_at')->get();

        $rows = $employees->map(function ($emp) use ($start, $end) {
            $data = $this->computeForEmployee($emp, $start, $end);
            return array_merge(['employee' => $emp], $data);
        });

        return view('hr.payroll.index', [
            'rows' => $rows,
            'year' => $year,
            'month' => $month,
            'period_start' => $start,
            'period_end' => $end,
        ]);
    }

    public function show(Employee $employee, Request $request)
    {
        // sanitize year/month inputs
        $year = (int) $request->query('year', Carbon::now()->year);
        $month = (int) $request->query('month', Carbon::now()->month);
        $now = Carbon::now();
        if ($year < 1970 || $year > $now->year + 1) {
            $year = $now->year;
        }
        if ($month < 1 || $month > 12) {
            $month = $now->month;
        }

        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = (clone $start)->endOfMonth()->endOfDay();

        $data = $this->computeForEmployee($employee, $start, $end);

        return view('hr.payroll.show', array_merge([
            'employee' => $employee,
            'period_start' => $start,
            'period_end' => $end,
        ], $data));
    }

    public function pay(Employee $employee, Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:1970|max:' . (Carbon::now()->year + 1),
            'month' => 'required|integer|min:1|max:12',
            'adjustments' => 'nullable|numeric',
        ]);

        $start = Carbon::createFromDate((int) $request->input('year'), (int) $request->input('month'), 1)->startOfDay();
        $end = (clone $start)->endOfMonth()->endOfDay();


        $calc = $this->computeForEmployee($employee, $start, $end);

        // prevent duplicate payment for same employee & period
        $existing = Salary::where('employee_id', $employee->id)
            ->where('period_start', $start->toDateString())
            ->where('period_end', $end->toDateString())
            ->first();
        if ($existing) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'exists',
                    'message' => 'Salary already recorded for this period',
                    'salary_id' => $existing->id,
                    'final_salary' => $existing->final_salary ?? $existing->net_pay,
                ], 200);
            }
            return redirect()->route('hr.payroll.index', ['year' => $request->input('year'), 'month' => $request->input('month')])
                ->with('status', 'Salary already recorded for ' . $employee->first_name . ' ' . $employee->last_name)
                ->with('print_salary_id', $existing->id);
        }

        $adjustments = $request->input('adjustments', 0);

        // final salary after absence deductions (we store final_salary separately)
        $finalSalary = round($calc['net_pay'] + $adjustments, 2);

        $payload = [
            'employee_id' => $employee->id,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'base_salary' => $calc['base_salary'],
            'adjustments' => $adjustments,
            'bonuses' => $calc['bonuses'],
            'deductions' => $calc['deductions'],
            'net_pay' => $finalSalary,
            'generated_at' => Carbon::now(),
        ];

        // Conditionally include the new absence/final fields only if the DB has them
        if (Schema::hasColumn('salaries', 'absent_days_without_reason')) {
            $payload['absent_days_without_reason'] = $calc['absent_without_reason'] ?? 0;
        }
        if (Schema::hasColumn('salaries', 'absent_days_with_reason')) {
            $payload['absent_days_with_reason'] = $calc['absent_with_reason'] ?? 0;
        }
        if (Schema::hasColumn('salaries', 'final_salary')) {
            $payload['final_salary'] = $finalSalary;
        }

        $salary = Salary::create($payload);

        // If request expects JSON (AJAX), return JSON payload so client can update UI without reload
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Salary recorded',
                'salary_id' => $salary->id,
                'employee_id' => $employee->id,
                'final_salary' => $salary->final_salary,
            ]);
        }

        // Otherwise, normal redirect with flash
        return redirect()->route('hr.payroll.index', ['year' => $request->input('year'), 'month' => $request->input('month')])
            ->with('status', 'Salary recorded for ' . $employee->first_name . ' ' . $employee->last_name)
            ->with('print_salary_id', $salary->id);
    }

    public function print(Salary $salary)
    {
        $salary->load('employee');

        // Prefer returning a PDF for printing (uses barryvdh/laravel-dompdf if available)
        try {
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('hr.payroll.print', ['salary' => $salary]);
                // stream inline so browser opens PDF viewer (user can print)
                return $pdf->stream('salary_' . $salary->id . '.pdf');
            }
        } catch (\Exception $e) {
            // fall back to HTML view below
        }

        return view('hr.payroll.print', ['salary' => $salary]);
    }

    /**
     * Export payroll report for a given month/year as PDF (or fallback CSV).
     */
    public function export(Request $request)
    {
        $year = (int) $request->query('year', Carbon::now()->year);
        $month = (int) $request->query('month', Carbon::now()->month);
        $now = Carbon::now();
        if ($year < 1970 || $year > $now->year + 1) $year = $now->year;
        if ($month < 1 || $month > 12) $month = $now->month;

        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = (clone $start)->endOfMonth()->endOfDay();

        $employees = Employee::whereNull('deleted_at')->get();
        $rows = $employees->map(function ($emp) use ($start, $end) {
            $data = $this->computeForEmployee($emp, $start, $end);
            return array_merge(['employee' => $emp], $data);
        })->toArray();

        // If dompdf available, make PDF
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            // Use the reports style (same visual style as attendance PDF) and include absence details
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('hr.reports.payroll_pdf', [
                'rows' => $rows,
                'year' => $year,
                'month' => $month,
                // optional filters kept for parity with attendance report
                'filters' => ['year' => $year, 'month' => $month],
            ]);
            return $pdf->download("payroll_report_{$year}_{$month}.pdf");
        }

        // fallback CSV
        $csv = "#,Employee,Department,Base Salary,Bonuses,Deductions,Absent Days,Absent Deduction,Final Salary\n";
        foreach ($rows as $i => $r) {
            $dept = $r['employee']->department->name ?? '';
            $csv .= implode(',', [
                $i+1,
                '"' . ($r['employee']->first_name . ' ' . $r['employee']->last_name) . '"',
                '"' . $dept . '"',
                $r['base_salary'],
                $r['bonuses'],
                $r['deductions'],
                $r['absence_count'] ?? 0,
                $r['absence_deduction'] ?? 0,
                $r['final_salary'] ?? $r['net_pay'],
            ]) . "\n";
        }
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=payroll_report_{$year}_{$month}.csv",
        ]);
    }

    private function computeForEmployee(Employee $employee, Carbon $start, Carbon $end)
    {
        $base = $employee->salary ?? 0;

        $bonuses = RewardPenalty::where('employee_id', $employee->id)
            ->where('type', 'reward')
            ->whereBetween('issued_at', [$start->toDateString(), $end->toDateString()])
            ->sum('amount') ?: 0;

        $deductions = RewardPenalty::where('employee_id', $employee->id)
            ->where('type', 'penalty')
            ->whereBetween('issued_at', [$start->toDateString(), $end->toDateString()])
            ->sum('amount') ?: 0;

        $adjustments = 0; // manual adjustments entered at payment time

        // Classify absences into with-reason (approved leave) and without-reason
        $absentDates = \App\Models\Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'absent')
            ->pluck('date')
            ->map(function ($d) { return (string) $d; })
            ->unique()
            ->values()
            ->all();

        $absentWithReason = 0;
        $absentWithoutReason = 0;
        foreach ($absentDates as $d) {
            $hasApprovedLeave = \App\Models\LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where('start_date', '<=', $d)
                ->where('end_date', '>=', $d)
                ->exists();

            if ($hasApprovedLeave) {
                $absentWithReason++;
            } else {
                $absentWithoutReason++;
            }
        }

        // Deduction rules: without reason -> 2 days deducted per absence; with reason -> 1 day per absence
        $deductionDays = ($absentWithoutReason * 2) + ($absentWithReason * 1);

        // Salary calculation basis: 30 days in a month (per requirement)
        $perDay = $base / 30.0;
        $absenceDeduction = round($perDay * $deductionDays, 2);

        // include absence deduction into deductions total
        $deductions = round($deductions + $absenceDeduction, 2);

        // Final/Net salary (include other bonuses/deductions)
        $net = round($base + $bonuses - $deductions + $adjustments, 2);

        // existing salary record for this period (if any)
        $salary = Salary::where('employee_id', $employee->id)
            ->where('period_start', $start->toDateString())
            ->where('period_end', $end->toDateString())
            ->first();

        return [
            'base_salary' => $base,
            'bonuses' => $bonuses,
            'deductions' => $deductions,
            'adjustments' => $adjustments,
            'absence_count' => count($absentDates),
            'absence_deduction' => $absenceDeduction,
            'absent_with_reason' => $absentWithReason,
            'absent_without_reason' => $absentWithoutReason,
            'deduction_days' => $deductionDays,
            'net_pay' => $net,
            'salary' => $salary,
        ];
    }
}
