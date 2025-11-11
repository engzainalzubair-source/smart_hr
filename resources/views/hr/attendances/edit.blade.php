@if(request()->ajax() || request()->query('inline'))
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">Edit Attendance Record</h2>
        <form id="ajaxForm" method="POST" action="{{ route('hr.attendances.update', $attendance) }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="PUT">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Employee</label>
                    <select name="employee_id" class="w-full p-2 border rounded">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $attendance->employee_id == $emp->id ? 'selected' : '' }}>{{ $emp->first_name }} {{ $emp->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Date</label>
                    <input type="date" name="date" value="{{ $attendance->date }}" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>Check In</label>
                    <input type="time" name="check_in" step="1" value="{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '' }}" placeholder="HH:MM:SS" class="w-full p-2 border rounded time-input">
                </div>
                <div>
                    <label>Check Out</label>
                    <input type="time" name="check_out" step="1" value="{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '' }}" placeholder="HH:MM:SS" class="w-full p-2 border rounded time-input">
                </div>
                <div>
                    <label>Status</label>
                    <select name="status" class="w-full p-2 border rounded">
                        <option value="present" {{ $attendance->status=='present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ $attendance->status=='absent' ? 'selected' : '' }}>Absent</option>
                        <option value="late" {{ $attendance->status=='late' ? 'selected' : '' }}>Late</option>
                        <option value="remote" {{ $attendance->status=='remote' ? 'selected' : '' }}>Remote</option>
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
            <h2 class="text-lg font-semibold mb-4">Edit Attendance Record</h2>
            <form method="POST" action="{{ route('hr.attendances.update', $attendance) }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label>Employee</label>
                        <select name="employee_id" class="w-full p-2 border rounded">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $attendance->employee_id == $emp->id ? 'selected' : '' }}>{{ $emp->first_name }} {{ $emp->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Date</label>
                        <input type="date" name="date" value="{{ $attendance->date }}" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>Check In</label>
                        <input type="time" name="check_in" step="1" value="{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '' }}" placeholder="HH:MM:SS" class="w-full p-2 border rounded time-input">
                    </div>
                    <div>
                        <label>Check Out</label>
                        <input type="time" name="check_out" step="1" value="{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '' }}" placeholder="HH:MM:SS" class="w-full p-2 border rounded time-input">
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status" class="w-full p-2 border rounded">
                            <option value="present" {{ $attendance->status=='present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ $attendance->status=='absent' ? 'selected' : '' }}>Absent</option>
                            <option value="late" {{ $attendance->status=='late' ? 'selected' : '' }}>Late</option>
                            <option value="remote" {{ $attendance->status=='remote' ? 'selected' : '' }}>Remote</option>
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
