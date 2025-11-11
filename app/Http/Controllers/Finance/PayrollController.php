<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Salary;

class PayrollController extends Controller
{
    public function index()
    {
        $salaries = Salary::with('employee')->latest()->paginate(20);
        return view('finance.index', compact('salaries'));
    }

    // Generate payroll for a given period (very basic example)
    public function generate(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');

        $employees = Employee::where('status', 'active')->get();
        foreach ($employees as $emp) {
            // Placeholder net-pay calculation
            $base = $emp->salary ?? 0;
            $net = $base; // Real calc should include attendance, bonuses, deductions
            Salary::create([
                'employee_id' => $emp->id,
                'period_start' => $start,
                'period_end' => $end,
                'base_salary' => $base,
                'net_pay' => $net,
            ]);
        }

        return redirect()->back();
    }
}
