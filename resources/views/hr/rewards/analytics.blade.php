@extends('layouts.hr')

@section('hr-content')
<div class="bg-white rounded-xl p-6 shadow">
    <h3 class="text-lg font-semibold mb-4">Rewards & Penalties Analytics</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 border rounded">
            <h4 class="font-medium">Totals</h4>
            <div class="mt-2">
                <div>Rewards: {{ number_format(optional($totals->get('reward'))->total ?? 0,2) }}</div>
                <div>Penalties: {{ number_format(optional($totals->get('penalty'))->total ?? 0,2) }}</div>
            </div>
        </div>
        <div class="p-4 border rounded col-span-2">
            <h4 class="font-medium">By Department</h4>
            <div class="mt-2">
                <ul class="space-y-2">
                    @foreach($byDept as $row)
                    <li class="flex justify-between"><div>{{ $row->department }} ({{ $row->type }})</div><div>{{ number_format($row->total,2) }}</div></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <!-- Recommendations are available inline on the rewards list page. -->
        <a href="{{ route('hr.rewards.index') }}" class="inline-block ml-2 px-4 py-2 bg-gray-200 text-gray-800 rounded">Back to list</a>
    </div>
</div>
@endsection
