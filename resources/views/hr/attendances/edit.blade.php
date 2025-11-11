@if(request()->ajax() || request()->query('inline'))
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">تعديل سجل الحضور</h2>
        <form id="ajaxForm" method="POST" action="{{ route('hr.attendances.update', $attendance) }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="PUT">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>الموظف</label>
                    <select name="employee_id" class="w-full p-2 border rounded">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $attendance->employee_id == $emp->id ? 'selected' : '' }}>{{ $emp->first_name }} {{ $emp->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>التاريخ</label>
                    <input type="date" name="date" value="{{ $attendance->date }}" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>دخول</label>
                    <input type="time" name="check_in" value="{{ $attendance->check_in }}" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>خروج</label>
                    <input type="time" name="check_out" value="{{ $attendance->check_out }}" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>الحالة</label>
                    <select name="status" class="w-full p-2 border rounded">
                        <option value="present" {{ $attendance->status=='present' ? 'selected' : '' }}>حاضر</option>
                        <option value="absent" {{ $attendance->status=='absent' ? 'selected' : '' }}>غائب</option>
                        <option value="late" {{ $attendance->status=='late' ? 'selected' : '' }}>متأخر</option>
                        <option value="remote" {{ $attendance->status=='remote' ? 'selected' : '' }}>عن بُعد</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
            </div>
        </form>
    </div>
@else
    @extends('layouts.app')

    @section('content')
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">تعديل سجل الحضور</h2>
            <form method="POST" action="{{ route('hr.attendances.update', $attendance) }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label>الموظف</label>
                        <select name="employee_id" class="w-full p-2 border rounded">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $attendance->employee_id == $emp->id ? 'selected' : '' }}>{{ $emp->first_name }} {{ $emp->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>التاريخ</label>
                        <input type="date" name="date" value="{{ $attendance->date }}" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>دخول</label>
                        <input type="time" name="check_in" value="{{ $attendance->check_in }}" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>خروج</label>
                        <input type="time" name="check_out" value="{{ $attendance->check_out }}" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>الحالة</label>
                        <select name="status" class="w-full p-2 border rounded">
                            <option value="present" {{ $attendance->status=='present' ? 'selected' : '' }}>حاضر</option>
                            <option value="absent" {{ $attendance->status=='absent' ? 'selected' : '' }}>غائب</option>
                            <option value="late" {{ $attendance->status=='late' ? 'selected' : '' }}>متأخر</option>
                            <option value="remote" {{ $attendance->status=='remote' ? 'selected' : '' }}>عن بُعد</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
                </div>
            </form>
        </div>
    @endsection
@endif
