<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // List employees
    public function index()
    {
        // include trashed employees so UI can show archived state and allow restore
        $employees = Employee::with('department')->withTrashed()->paginate(15);
        if (request()->ajax()) {
            return view('hr.partials.employees_table', compact('employees'));
        }
        return view('hr.employees.index', compact('employees'));
    }

    // Show create form
    public function create()
    {
        return view('hr.employees.create');
    }

    // Store employee
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'status' => 'nullable|string',
        ]);
        Employee::create($data);
        return redirect()->route('hr.employees.index');
    }

    // Edit form
    public function edit(Employee $employee)
    {
        return view('hr.employees.edit', compact('employee'));
    }

    /**
     * Show employee profile with tabs: personal, attendance, performance, rewards
     */
    public function profile(Employee $employee)
    {
        $employee->load('department');
        $attendances = $employee->attendances()->latest()->limit(100)->get();
        $performances = $employee->performances()->latest()->limit(50)->get();
        $rewards = $employee->rewardsPenalties()->latest()->limit(50)->get();

        $routeName = request()->route() ? request()->route()->getName() : '';
        $isEmployeeModule = Str::startsWith($routeName, 'employees.');

        $user = request()->user();
        $canEdit = false;
        if ($user) {
            if (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('hr'))) {
                $canEdit = true;
            } else {
                if ($user->email && $employee->email && strtolower($user->email) === strtolower($employee->email)) {
                    $canEdit = true;
                }
            }
        }

        return view('hr.employees.profile', compact('employee','attendances','performances','rewards','isEmployeeModule','canEdit'));
    }

    // Update
    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'status' => 'nullable|string',
        ]);
        $employee->update($data);
        return redirect()->route('hr.employees.index');
    }

    // Archive (soft-delete behavior can be implemented separately)
    public function archive(Employee $employee)
    {
        $employee->delete();
        return back();
    }

    // Restore archived employee
    public function restore($id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);
        $employee->restore();
        // optionally reset status
        $employee->update(['status' => 'active']);
        return back();
    }

    /**
     * Update limited contact/profile fields (address, phone) via AJAX
     */
    public function updateContact(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
        ]);

        $employee->update($data);

        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'message' => 'تم تحديث بيانات الموظف', 'employee' => $employee->only(['phone','address'])]);
        }
        return redirect()->back()->with('success','Employee contact updated');
    }
}
