@if(request()->ajax() || request()->query('inline'))
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">Edit Employee</h2>
        <form id="ajaxForm" method="POST" action="{{ route('hr.employees.update', $employee) }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="PUT">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>First Name</label>
                    <input type="text" name="first_name" value="{{ $employee->first_name }}" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="{{ $employee->last_name }}" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" value="{{ $employee->email }}" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>Hire Date</label>
                    <input type="date" name="hire_date" value="{{ optional($employee->hire_date)->format('Y-m-d') ?? '' }}" lang="en" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>Salary</label>
                    <input type="number" step="0.01" name="salary" value="{{ $employee->salary }}" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>Status</label>
                    <select name="status" class="w-full p-2 border rounded">
                        <option value="active" {{ $employee->status=='active' ? 'selected' : '' }}>Active</option>
                        <option value="archived" {{ $employee->status=='archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>
@else
    @extends('layouts.app')

    @section('content')
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">Edit Employee</h2>
            <form method="POST" action="{{ route('hr.employees.update', $employee) }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label>First Name</label>
                        <input type="text" name="first_name" value="{{ $employee->first_name }}" class="w-full p-2 border rounded" required>
                    </div>
                    <div>
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="{{ $employee->last_name }}" class="w-full p-2 border rounded" required>
                    </div>
                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="{{ $employee->email }}" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>Hire Date</label>
                        <input type="date" name="hire_date" value="{{ optional($employee->hire_date)->format('Y-m-d') ?? '' }}" lang="en" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>Salary</label>
                        <input type="number" step="0.01" name="salary" value="{{ $employee->salary }}" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status" class="w-full p-2 border rounded">
                            <option value="active" {{ $employee->status=='active' ? 'selected' : '' }}>Active</option>
                            <option value="archived" {{ $employee->status=='archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    @endsection
@endif
