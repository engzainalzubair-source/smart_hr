@extends('layouts.hr')

@section('hr-content')
    <div class="max-w-6xl mx-auto py-6" dir="ltr">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Departments</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.departments.create') }}" class="px-3 py-2 bg-indigo-600 text-white rounded">Add Department</a>
                <a href="{{ route('hr.departments.exportPdf') }}" class="px-3 py-2 bg-red-600 text-white rounded" target="_blank">Download PDF (table)</a>
                <button onclick="window.print()" class="px-3 py-2 bg-gray-200 text-gray-800 rounded">Print</button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700">{{ session('success') }}</div>
        @endif

        <!-- Analytics summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Total Departments</div>
                <div class="text-2xl font-bold">{{ $totalDepartments ?? 0 }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Total Employees</div>
                <div class="text-2xl font-bold">{{ $totalEmployees ?? 0 }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Total Payroll</div>
                <div class="text-2xl font-bold">{{ number_format($totalPayroll ?? 0, 2) }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Average Salary</div>
                <div class="text-2xl font-bold">{{ number_format($avgSalaryOverall ?? 0, 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <div class="bg-white p-4 rounded shadow">
                <h3 class="text-sm font-semibold mb-2">Top Departments by Performance (last 90 days)</h3>
                <ul class="space-y-2">
                    @forelse($topByPerf ?? [] as $t)
                        <li class="flex justify-between">
                            <div>{{ $t['name'] }}</div>
                            <div class="font-semibold">{{ $t['avg_perf'] }}%</div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">No sufficient data</li>
                    @endforelse
                </ul>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h3 class="text-sm font-semibold mb-2">Top Payroll Expenses</h3>
                <ul class="space-y-2">
                    @forelse($topByPayroll ?? [] as $p)
                        <li class="flex justify-between">
                            <div>{{ $p['name'] }}</div>
                            <div class="font-semibold">{{ number_format($p['payroll'], 2) }}</div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">No sufficient data</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="bg-white p-4 rounded shadow-sm">
            <div class="overflow-x-auto" dir="ltr">
                <table class="min-w-full divide-y divide-gray-200" dir="ltr">
                    <thead>
                        <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Department</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-gray-700" style="width:70px">Employees</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-gray-700" style="width:120px">Total Salaries</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-gray-700" style="width:110px">Average Salary</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-gray-700" style="width:100px">Max Salary</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-gray-700" style="width:100px">Min Salary</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-gray-700" style="width:80px">Avg Performance</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-gray-700" style="width:140px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($departments as $dept)
                            @php $s = $deptStats[$dept->id] ?? null; @endphp
                            <tr>
                                <td class="px-4 py-3">{{ $dept->name }}</td>
                                <td class="px-4 py-3 text-right" style="width:70px">{{ $s['employees_count'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right" style="width:120px">{{ number_format($s['total_salary'] ?? 0, 2) }}</td>
                                <td class="px-4 py-3 text-right" style="width:110px">{{ number_format($s['avg_salary'] ?? 0, 2) }}</td>
                                <td class="px-4 py-3 text-right" style="width:100px">{{ number_format($s['max_salary'] ?? 0, 2) }}</td>
                                <td class="px-4 py-3 text-right" style="width:100px">{{ number_format($s['min_salary'] ?? 0, 2) }}</td>
                                <td class="px-4 py-3 text-right" style="width:80px">{{ $s['avg_performance'] ?? 0 }}%</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('hr.departments.edit', $dept) }}" class="text-sm text-indigo-600 mr-3">Edit</a>
                                    <form action="{{ route('hr.departments.destroy', $dept) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this department?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500">لا توجد أقسام.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $departments->links() }}
            </div>
        </div>
    </div>
@endsection

