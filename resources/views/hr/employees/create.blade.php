@if(request()->ajax() || request()->query('inline'))
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">Add New Employee</h2>
        <form id="ajaxForm" method="POST" action="{{ route('hr.employees.store') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>First Name</label>
                    <input type="text" name="first_name" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>Hire Date</label>
                    <input type="date" name="hire_date" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>Salary</label>
                    <input type="number" step="0.01" name="salary" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>Status</label>
                    <select name="status" class="w-full p-2 border rounded">
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
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
            <h2 class="text-lg font-semibold mb-4">Add New Employee</h2>
            <form method="POST" action="{{ route('hr.employees.store') }}">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label>First Name</label>
                        <input type="text" name="first_name" class="w-full p-2 border rounded" required>
                    </div>
                    <div>
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="w-full p-2 border rounded" required>
                    </div>
                    <div>
                        <label>Email</label>
                        <input type="email" name="email" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>Hire Date</label>
                        <input type="date" name="hire_date" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>Salary</label>
                        <input type="number" step="0.01" name="salary" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status" class="w-full p-2 border rounded">
                            <option value="active">Active</option>
                            <option value="archived">Archived</option>
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
