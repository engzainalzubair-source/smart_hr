@extends('layouts.hr')

@section('hr-content')
    <div class="bg-white rounded-xl p-6 shadow">
    <h3 class="text-lg font-semibold mb-4">Auto Recommendations</h3>
    @if(empty($suggestions) || count($suggestions) == 0)
        <div class="text-sm text-gray-500">No recommendations generated for the selected period.</div>
    @else
    <table class="min-w-full table-auto">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Score</th>
                    <th>Suggested Amount</th>
                    <th>Metadata</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suggestions as $s)
                    @php
                        $emp = App\Models\Employee::find($s['employee_id']);
                        $empName = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : ('Employee #' . ($s['employee_id'] ?? ''));
                        $amount = number_format($s['suggested_amount'] ?? 0, 2);
                        $meta = isset($s['metadata']) ? json_encode($s['metadata'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : '';
                    @endphp
                <tr>
                    <td>{{ $empName }}</td>
                    <td>{{ ucfirst($s['type'] ?? '') }}</td>
                    <td>{{ $s['score'] ?? '' }}</td>
                    <td>{{ $amount }}</td>
                    <td><pre class="text-xs">{{ $meta }}</pre></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
