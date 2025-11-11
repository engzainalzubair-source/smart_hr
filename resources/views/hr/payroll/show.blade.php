@extends('layouts.hr')

@section('hr-content')
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-800">Salary Details — {{ $employee->first_name }} {{ $employee->last_name }}</h2>
                <p class="text-sm text-gray-500">Period: {{ $period_start->toDateString() }} - {{ $period_end->toDateString() }}</p>
            </div>
            <div>
                <a href="{{ route('hr.payroll.index', ['year' => $period_start->year, 'month' => $period_start->month]) }}" class="px-3 py-1 bg-gray-100 rounded">Back</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="col-span-2">
                <div class="p-4 border rounded bg-gray-50">
                    <table class="w-1/2 text-right">
                        <tr><th class="text-sm text-gray-700">Base Salary</th><td class="font-medium">{{ number_format($base_salary,2) }}</td></tr>
                        <tr><th class="text-sm text-gray-700">Bonuses</th><td class="font-medium">{{ number_format($bonuses,2) }}</td></tr>
                        <tr><th class="text-sm text-gray-700">Deductions (including absence)</th><td class="font-medium">{{ number_format($deductions,2) }}</td></tr>
                        <tr><th class="text-sm text-gray-700">- Absence Deduction</th><td class="font-medium">{{ number_format($absence_deduction ?? 0,2) }} ({{ $absence_count ?? 0 }} days)</td></tr>
                        <tr><th class="text-sm text-gray-700">Manual Adjustments</th><td class="font-medium">{{ number_format($adjustments,2) }}</td></tr>
                        <tr><th class="text-sm text-gray-700">Expected Net</th><td class="font-semibold text-indigo-600">{{ number_format($net_pay,2) }}</td></tr>
                        <tr><th class="text-sm text-gray-700">Final Salary After Adjustments</th><td class="font-semibold text-indigo-600">{{ number_format($salary->final_salary ?? ($net_pay ?? 0),2) }}</td></tr>
                    </table>
                </div>

                <div class="mt-4 w-1/2">
                    <label class="block text-sm text-gray-700">Adjustments (+/-)</label>
                    <input class="px-3 py-2 border rounded w-full bg-gray-100" value="{{ $adjustments ?? 0 }}" disabled />

                    <div class="mt-3 flex items-center gap-2">
                        @if(session('status'))
                            <div class="text-green-600">{{ session('status') }}</div>
                        @endif
                        @if(!empty($salary))
                            <a href="{{ route('hr.payroll.print', $salary->id) }}" target="_blank" class="px-3 py-1 bg-indigo-600 text-white rounded">Print Slip</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="p-4 border rounded shadow-sm bg-gray-50">
                    <h4 class="font-semibold mb-2 text-gray-700">Notes</h4>
                    <p class="text-sm text-gray-600">You can add manual adjustments to record an advance or pay extra amounts. For better tracking consider creating a dedicated advances record if you plan to deduct installments automatically.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

