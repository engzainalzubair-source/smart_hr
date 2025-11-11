<div class="p-4 border rounded shadow-sm bg-gray-50">
    <h4 class="font-semibold mb-2 text-gray-700">Recommendations</h4>
    @if(empty($recommendations))
        <div class="text-sm text-gray-600">No recommendations at this time.</div>
    @else
        <ul class="space-y-3">
            @foreach(array_slice($recommendations,0,8) as $rec)
                <li class="text-sm">
                    <div class="font-medium text-gray-800">{{ $rec['name'] }} <span class="text-xs text-gray-500">({{ $rec['composite'] }}%)</span></div>
                    <div class="text-gray-600">Issue: <strong>{{ $rec['issue'] }}</strong></div>
                    <div class="text-gray-700">Action: {{ $rec['suggestion'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Confidence: {{ $rec['confidence'] }}%</div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
