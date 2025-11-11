<div class="bg-white p-4 rounded shadow-sm">
    <h3 class="text-lg font-medium mb-4">Departments</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Name</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Description</th>
                    <th class="px-3 py-2 text-right text-sm font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach(App\Models\Department::orderBy('name')->limit(10)->get() as $dept)
                    <tr>
                        <td class="px-3 py-2">{{ $dept->name }}</td>
                        <td class="px-3 py-2">{{ Str::limit($dept->description, 80) }}</td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('hr.departments.edit', $dept) }}" class="text-sm text-indigo-600 mr-2">Edit</a>
                            <form action="{{ route('hr.departments.destroy', $dept) }}" method="POST" class="inline" onsubmit="return confirm('Delete this department?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
