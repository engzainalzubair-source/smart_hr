@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold">Create Department</h2>
            <a href="{{ route('hr.departments.index') }}" class="text-sm text-gray-600">Back to list</a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700">{{ session('success') }}</div>
        @endif

        <div class="bg-white p-6 rounded shadow-sm">
            <form action="{{ route('hr.departments.store') }}" method="POST">
                @include('hr.departments._form', ['buttonText' => 'Create'])
            </form>
        </div>
    </div>
@endsection
