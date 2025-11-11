@if(request()->ajax() || request()->query('inline'))
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">سجل حضور جديد</h2>
        <form id="ajaxForm" method="POST" action="{{ route('hr.attendances.store') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>الموظف</label>
                    <select name="employee_id" class="w-full p-2 border rounded">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>التاريخ</label>
                    <input type="date" name="date" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>دخول</label>
                    <input type="time" name="check_in" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>خروج</label>
                    <input type="time" name="check_out" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>الحالة</label>
                    <select name="status" class="w-full p-2 border rounded">
                        <option value="present">حاضر</option>
                        <option value="absent">غائب</option>
                        <option value="late">متأخر</option>
                        <option value="remote">عن بُعد</option>
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
            <h2 class="text-lg font-semibold mb-4">سجل حضور جديد</h2>
            <form method="POST" action="{{ route('hr.attendances.store') }}">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label>الموظف</label>
                        <select name="employee_id" class="w-full p-2 border rounded">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>التاريخ</label>
                        <input type="date" name="date" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>دخول</label>
                        <input type="time" name="check_in" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>خروج</label>
                        <input type="time" name="check_out" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>الحالة</label>
                        <select name="status" class="w-full p-2 border rounded">
                            <option value="present">حاضر</option>
                            <option value="absent">غائب</option>
                            <option value="late">متأخر</option>
                            <option value="remote">عن بُعد</option>
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
