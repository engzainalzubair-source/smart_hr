<?php

namespace App\Http\Controllers\Employees;

use App\Http\Controllers\HR\EmployeeController as HREmployeeController;

class EmployeeController extends HREmployeeController
{
    // This class intentionally extends the HR EmployeeController so the
    // same methods (profile, updateContact, etc.) can be reused while
    // giving a separate namespace/folder for "Employees" module.

    /**
     * Allow an employee (or admin/hr) to update their full profile.
     */
    public function selfUpdate(\Illuminate\Http\Request $request, \App\Models\Employee $employee)
    {
        $user = $request->user();

        // Authorization: admin/hr can edit anyone; otherwise user must match employee email
        $canEdit = false;
        if (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('hr'))) {
            $canEdit = true;
        } else {
            if ($user->email && $employee->email && strtolower($user->email) === strtolower($employee->email)) {
                $canEdit = true;
            }
        }

        if (! $canEdit) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'job_title' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'status' => 'nullable|string',
        ]);

        $employee->update($data);

        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'message' => 'Profile updated', 'employee' => $employee]);
        }

        return redirect()->route('employees.profile', $employee->id)->with('success', 'Profile updated');
    }

    /**
     * Store a leave request submitted by the employee.
     */
    public function storeLeave(\Illuminate\Http\Request $request, \App\Models\Employee $employee)
    {
        $user = $request->user();

        // ensure employee/user match or user has admin/hr role
        $canSubmit = false;
        if (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('hr'))) {
            $canSubmit = true;
        } else {
            if ($user->email && $employee->email && strtolower($user->email) === strtolower($employee->email)) {
                $canSubmit = true;
            }
        }
        if (! $canSubmit) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $leave = \App\Models\LeaveRequest::create([
            'employee_id' => $employee->id,
            'user_id' => $user->id ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'type' => $data['type'] ?? 'annual',
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'message' => 'Leave request submitted', 'leave' => $leave]);
        }

        return redirect()->back()->with('success', 'Leave request submitted');
    }
}
