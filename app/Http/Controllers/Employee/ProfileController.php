<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;

class ProfileController extends Controller
{
    public function show(Employee $employee)
    {
        return view('employee.profile', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('employee.edit', compact('employee'));
    }

    public function update(
        \Illuminate\Http\Request $request,
        Employee $employee
    )
    {
        $employee->update($request->only(['address','phone']));
        return redirect()->route('employee.profile', $employee);
    }
}
