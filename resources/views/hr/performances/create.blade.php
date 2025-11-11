@if(request()->ajax() || request()->query('inline'))
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">إضافة تقرير أداء</h2>
        <form id="ajaxForm" method="POST" action="{{ route('hr.performances.store') }}">
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
                    <label>بداية الفترة</label>
                    <input type="date" name="period_start" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>نهاية الفترة</label>
                    <input type="date" name="period_end" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>النقاط</label>
                    <input type="number" step="0.1" name="score" class="w-full p-2 border rounded">
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
            <h2 class="text-lg font-semibold mb-4">إضافة تقرير أداء</h2>
            <form method="POST" action="{{ route('hr.performances.store') }}">
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
                        <label>بداية الفترة</label>
                        <input type="date" name="period_start" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>نهاية الفترة</label>
                        <input type="date" name="period_end" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>النقاط</label>
                        <input type="number" step="0.1" name="score" class="w-full p-2 border rounded">
                    </div>
                </div>
                <div class="mt-4">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
                </div>
            </form>
        </div>
    @endsection
@endif
