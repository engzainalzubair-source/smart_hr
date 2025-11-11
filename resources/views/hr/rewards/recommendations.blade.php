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
                <tr>
                    <td>{{ optional(App\Models\Employee::find($s['employee_id']))->first_name ?? $s['employee_id'] }} {{ optional(App\Models\Employee::find($s['employee_id']))->last_name ?? '' }}</td>
                    <td>{{ $s['type'] }}</td>
                    <td>{{ $s['score'] }}</td>
                    <td>{{ number_format($s['suggested_amount'],2) }}</td>
                    <td><pre class="text-xs">{{ json_encode($s['metadata']) }}</pre></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
