@csrf
<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $department->name ?? '') }}" required maxlength="191" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
        @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $department->description ?? '') }}</textarea>
        @error('description')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="pt-2">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">{{ $buttonText ?? 'Save' }}</button>
        <a href="{{ route('hr.departments.index') }}" class="ml-2 text-sm text-gray-600">Cancel</a>
    </div>
</div>
