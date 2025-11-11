@if(request()->ajax() || request()->query('inline'))
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">إضافة مكافأة/عقوبة</h2>
        <form id="ajaxForm" method="POST" action="{{ route('hr.rewards.store') }}">
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
                    <label>النوع</label>
                    <select name="type" class="w-full p-2 border rounded">
                        <option value="reward">Reward</option>
                        <option value="penalty">Penalty</option>
                    </select>
                </div>
                <div>
                    <label>المبلغ</label>
                    <input type="number" name="amount" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>السبب</label>
                    <input type="text" name="reason" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>تاريخ الإصدار</label>
                    <input type="date" name="issued_at" class="w-full p-2 border rounded">
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
            <h2 class="text-lg font-semibold mb-4">إضافة مكافأة/عقوبة</h2>
            <form method="POST" action="{{ route('hr.rewards.store') }}">
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
                        <label>النوع</label>
                        <select name="type" class="w-full p-2 border rounded">
                            <option value="reward">Reward</option>
                            <option value="penalty">Penalty</option>
                        </select>
                    </div>
                    <div>
                        <label>المبلغ</label>
                        <input type="number" name="amount" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>السبب</label>
                        <input type="text" name="reason" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>تاريخ الإصدار</label>
                        <input type="date" name="issued_at" class="w-full p-2 border rounded">
                    </div>
                </div>
                <div class="mt-4">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
                </div>
            </form>
        </div>
    @endsection
@endif
