<div class="bg-white p-4 rounded shadow">
    <div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold">Employees</h2>
    <form method="GET" action="{{ route('hr.employees.create') }}" class="inline-fetch" data-tab="employees" data-inline="true">
        <input type="hidden" name="inline" value="1" />
        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded">Add Employee</button>
    </form>
    </div>

    <table class="w-full text-right">
        <thead>
            <tr class="bg-gray-50">
                <th class="p-2">#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $emp)
                <tr class="border-t">
                    <td class="p-2">{{ $emp->id }}</td>
                    <td>{{ $emp->first_name }} {{ $emp->last_name }}</td>
                    <td>{{ $emp->email }}</td>
                    <td>{{ optional($emp->department)->name }}</td>
                    <td>
                        @if(in_array($emp->id, $todayPresentIds ?? []))
                            <span class="px-2 py-1 text-sm bg-green-100 text-green-800 rounded">Present</span>
                        @else
                            <span class="px-2 py-1 text-sm bg-gray-100 text-gray-700 rounded">Not Recorded</span>
                        @endif
                    </td>
                    <td class="space-x-2">
                        @if($emp->trashed())
                            <span class="px-2 py-1 text-sm bg-gray-200 text-gray-700 rounded">Archived</span>
                            <form method="POST" action="{{ route('hr.employees.restore', $emp->id) }}" class="inline-action inline-block" data-tab="employees">
                                @csrf
                                <button type="submit" class="text-green-600">Restore</button>
                            </form>
                        @else
                            <form method="GET" action="{{ route('hr.employees.edit', $emp) }}" class="inline-fetch inline-block" data-tab="employees" data-inline="true">
                                <input type="hidden" name="inline" value="1" />
                                <button type="submit" class="text-blue-600">Edit</button>
                            </form>
                            <form method="POST" action="{{ route('hr.employees.archive', $emp) }}" class="inline-action inline-block" data-tab="employees">
                                @csrf
                                <button type="submit" class="text-rose-600">Archive</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $employees->links() ?? '' }}
    </div>
</div>
