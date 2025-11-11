@extends('layouts.hr')

@section('hr-content')
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-800">صرف الرواتب</h2>
                <p class="text-sm text-gray-500">قائمة الرواتب المتوقعة حسب الشهر، مع إمكانية تسجيل صرف وطباعة سند.</p>
            </div>

            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('hr.payroll.index') }}" class="inline-flex items-center gap-2">
                    <input name="year" type="number" value="{{ $year }}" class="px-2 py-1 border rounded text-sm w-20" />
                    <input name="month" type="number" min="1" max="12" value="{{ $month }}" class="px-2 py-1 border rounded text-sm w-20" />
                    <button class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">عرض</button>
                </form>
                    {{-- Export payroll report for selected period --}}
                    <a href="{{ route('hr.payroll.export', ['year' => $year, 'month' => $month]) }}" class="px-3 py-1 bg-red-600 text-white rounded text-sm">تصدير تقرير</a>
            </div>
        </div>
            @if(session('status'))
                <div class="mb-4 p-3 rounded text-sm {{ session('status') ? 'bg-green-50 text-green-800' : '' }}">{{ session('status') }}</div>
            @endif

        <div class="overflow-x-auto border rounded-lg shadow-sm">
            <table class="w-full text-right table-auto border-collapse">
                <thead class="bg-gray-50 text-gray-600 text-sm">
                    <tr>
                        <th class="p-3 border">#</th>
                        <th class="p-3 border">الموظف</th>
                        <th class="p-3 border">الراتب الأساسي</th>
                        <th class="p-3 border">المكافآت</th>
                        <th class="p-3 border">الخصومات</th>
                        <th class="p-3 border">تعديلات</th>
                        <th class="p-3 border">الصافي</th>
                        <th class="p-3 border">الحالة</th>
                        <th class="p-3 border">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                        @php($emp = $row['employee'])
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 text-sm">{{ $i + 1 }}</td>
                            <td class="p-3 text-sm">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                            <td class="p-3 text-sm">{{ number_format($row['base_salary'],2) }}</td>
                            <td class="p-3 text-sm">{{ number_format($row['bonuses'],2) }}</td>
                            <td class="p-3 text-sm">{{ number_format($row['deductions'],2) }}</td>
                            <td class="p-3 text-sm">{{ number_format($row['adjustments'],2) }}</td>
                            <td class="p-3 text-sm font-semibold text-indigo-600">{{ number_format($row['final_salary'] ?? $row['net_pay'] ?? 0,2) }}</td>
                            <td class="p-3 text-sm">
                                <div>
                                    @if(!empty($row['salary']))
                                        <span class="text-green-600">صرف</span>
                                    @else
                                        <span class="text-yellow-600">لم يصرف</span>
                                    @endif
                                    @if(!empty($row['absence_count']) && $row['absence_count'] > 0)
                                        <div class="text-xs text-gray-500">غيابات: {{ $row['absence_count'] }} (خصم {{ number_format($row['absence_deduction'],2) }})</div>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('hr.payroll.show', ['employee' => $emp->id, 'year' => $year, 'month' => $month]) }}" class="px-3 py-1 bg-gray-100 rounded text-sm">تفاصيل</a>
                                    @if(!empty($row['salary']))
                                        <a href="{{ route('hr.payroll.print', $row['salary']->id) }}" target="_blank" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">طباعة</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            {{-- If controller flashed print_salary_id but popup blocked, show a visible link/button --}}
            @if(session('print_salary_id'))
                <div class="mt-4 p-3 bg-yellow-50 border rounded">
                    <span class="text-sm text-gray-700">تم تسجيل صرف الراتب. إذا لم تُفتَح نافذة الطباعة تلقائياً اضغط:</span>
                    <a href="{{ url('/hr/payroll/print/' . session('print_salary_id')) }}" target="_blank" class="ml-3 px-3 py-1 bg-indigo-600 text-white rounded text-sm">طباعة السند</a>
                </div>
            @endif
        </div>

    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        // If the controller flashed a salary id to print, open the print URL in a new tab/window.
        @if(session('print_salary_id'))
            try {
                const id = {{ session('print_salary_id') }};
                const url = '{{ url('/') }}' + '/hr/payroll/print/' + id;
                // open in new tab/window
                window.open(url, '_blank');
            } catch (e) {
                console.error('Failed to open print window', e);
            }
        @endif
    });
</script>
@endsection

{{-- removed pay-salary AJAX handler since pay button was removed --}}


