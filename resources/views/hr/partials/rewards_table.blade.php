@php use Illuminate\Support\Str; @endphp

<div class="bg-white p-4 rounded shadow">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <h2 class="text-lg font-semibold">Rewards & Penalties</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('hr.rewards.create') }}" class="px-3 py-1 bg-green-600 text-white rounded">Add</a>
            <a href="{{ route('hr.rewards.exportPdf') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" class="px-3 py-1 bg-gray-700 text-white rounded">Export PDF</a>
            <!-- Analytics and Recommendations are shown inline on this page. No separate navigation links. -->
        </div>
    </div>

    <!-- Toolbar: filters + actions -->
    <div class="mb-4 grid grid-cols-1 lg:grid-cols-3 gap-4 items-center">
        <form method="GET" action="{{ route('hr.rewards.index') }}" class="col-span-2 grid grid-cols-1 md:grid-cols-6 gap-2 items-end">
        <div>
            <label class="text-sm text-gray-600">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="mt-1 block w-full border rounded px-2 py-1" placeholder="Name or email" />
        </div>
        <div>
            <label class="text-sm text-gray-600">Type</label>
            <select name="type" class="mt-1 block w-full border rounded px-2 py-1">
                <option value="">All</option>
                <option value="reward" {{ request('type')=='reward'?'selected':'' }}>Reward</option>
                <option value="penalty" {{ request('type')=='penalty'?'selected':'' }}>Penalty</option>
            </select>
        </div>
        <div>
            <label class="text-sm text-gray-600">From</label>
            <input type="date" name="from" value="{{ request('from') }}" lang="en" class="mt-1 block w-full border rounded px-2 py-1" />
        </div>
        <div>
            <label class="text-sm text-gray-600">To</label>
            <input type="date" name="to" value="{{ request('to') }}" lang="en" class="mt-1 block w-full border rounded px-2 py-1" />
        </div>
        <div>
            <label class="text-sm text-gray-600">Department</label>
            <select name="department" class="mt-1 block w-full border rounded px-2 py-1">
                <option value="">All</option>
                @foreach($departments ?? [] as $d)
                <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-1">
            <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded">Filter</button>
        </div>
        </form>

        <div class="flex gap-2 justify-end">
            <a href="{{ route('hr.rewards.create') }}" class="px-3 py-1 bg-green-600 text-white rounded">Add</a>
            <a href="{{ route('hr.rewards.exportPdf') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" class="px-3 py-1 bg-gray-700 text-white rounded">Export PDF</a>
        </div>
    </div>

    <!-- Inline Analytics (separate section) -->
    <section id="analyticsPanel" class="mb-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 border rounded">
            <div class="text-sm text-gray-600">Total Rewards</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format(optional($totals->get('reward'))->total ?? 0,2) }}</div>
            <div class="text-xs text-gray-500">Count: {{ optional($totals->get('reward'))->count ?? 0 }}</div>
        </div>
        <div class="p-4 border rounded">
            <div class="text-sm text-gray-600">Total Penalties</div>
            <div class="text-2xl font-bold text-red-600">{{ number_format(optional($totals->get('penalty'))->total ?? 0,2) }}</div>
            <div class="text-xs text-gray-500">Count: {{ optional($totals->get('penalty'))->count ?? 0 }}</div>
        </div>
        <div class="p-4 border rounded">
            <div class="text-sm text-gray-600">Net (Rewards - Penalties)</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format((optional($totals->get('reward'))->total ?? 0) - (optional($totals->get('penalty'))->total ?? 0),2) }}</div>
            <div class="text-xs text-gray-500">Quick view based on current filters</div>
        </div>
    </div>
    <!-- Departments breakdown -->
    <div class="mb-4 p-4 border rounded bg-gray-50">
        <h4 class="text-sm font-medium mb-2">Breakdown by Department (Summary)</h4>
        @php
            $deptMap = [];
            foreach($byDept ?? [] as $r) {
                $d = $r->department ?? 'Unknown';
                $type = $r->type ?? 'reward';
                $amt = $r->total ?? 0;
                if (!isset($deptMap[$d])) $deptMap[$d] = ['reward'=>0,'penalty'=>0];
                $deptMap[$d][$type] = ($deptMap[$d][$type] ?? 0) + $amt;
            }
            arsort($deptMap);
            $top = array_slice($deptMap,0,6,true);
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
            @foreach($top as $deptName => $vals)
            <div class="p-2 bg-white rounded border">
                <div class="text-sm font-semibold">{{ $deptName }}</div>
                <div class="text-xs text-green-700">Rewards: {{ number_format($vals['reward'] ?? 0,2) }}</div>
                <div class="text-xs text-red-700">Penalties: {{ number_format($vals['penalty'] ?? 0,2) }}</div>
            </div>
            @endforeach
            @if(empty($top))
            <div class="text-sm text-gray-500">Not enough data available</div>
            @endif
        </div>
    </div>
    </section>

    <!-- Recommendations section moved below the main table -->

    <div class="overflow-x-auto">
    <table class="w-full text-right table-auto">
        <thead>
            <tr class="bg-gray-50">
                <th class="p-2">#</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Reason</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $it)
                <tr class="border-t hover:bg-gray-50">
                    <td class="p-2">{{ $it->id }}</td>
                    <td>{{ optional($it->employee)->first_name }} {{ optional($it->employee)->last_name }}<div class="text-xs text-gray-500">{{ optional($it->employee)->email }}</div></td>
                    <td>{{ optional(optional($it->employee)->department)->name }}</td>
                    <td>
                        <span class="px-2 py-1 rounded {{ $it->type=='reward' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $it->type }}</span>
                    </td>
                    <td>{{ $it->value_type=='percentage' ? $it->amount . '%' : number_format($it->amount,2) }}</td>
                    <td class="max-w-xs">{{ Str::limit($it->reason, 60) }} @if($it->metadata) <button title="Details" onclick="document.getElementById('meta-{{ $it->id }}').classList.toggle('hidden')" class="text-xs text-indigo-600">Details</button>@endif
                        @if($it->metadata)
                        <pre id="meta-{{ $it->id }}" class="hidden text-xs bg-gray-50 p-2 rounded mt-1">{{ json_encode($it->metadata) }}</pre>
                        @endif
                    </td>
                    <td>{{ $it->issued_at ? $it->issued_at->format('Y-m-d') : '' }}</td>
                    <td>
                        @php $st = $it->status ?? 'approved'; @endphp
                        <span class="px-2 py-1 rounded text-sm {{ $st=='pending' ? 'bg-yellow-100 text-yellow-800' : ($st=='approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">{{ $st }}</span>
                    </td>
                    <td class="space-x-1">
                        @if(($it->status ?? '') == 'pending')
                        <form method="POST" action="{{ route('hr.rewards.approve', $it) }}" class="inline">
                            @csrf
                            <button type="submit" class="px-2 py-1 bg-green-600 text-white rounded text-sm">Approve</button>
                        </form>
                        @endif
                        <a href="{{ route('hr.rewards.edit', $it) }}" class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-sm">Edit</a>
                        <form method="POST" action="{{ route('hr.rewards.destroy', $it) }}" class="inline" onsubmit="return confirm('Confirm deletion?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded text-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <div class="mt-4">{{ $items->links() ?? '' }}</div>
</div>

@section('scripts')
<script>
    (function(){
        const analyticsToggle = document.getElementById('toggleAnalytics');
        const analyticsPanel = document.getElementById('analyticsPanel');
        const recToggle = document.getElementById('toggleRecommendations');
        const recPanel = document.getElementById('recommendationsPanel');
        const runBtn = document.getElementById('runRecommendations');
        const recContent = document.getElementById('recommendationsContent');

        if (analyticsToggle && analyticsPanel) {
            analyticsToggle.addEventListener('click', (e) => {
                e.preventDefault();
                analyticsPanel.classList.toggle('hidden');
            });
        }
        if (recToggle && recPanel) {
            recToggle.addEventListener('click', (e) => {
                e.preventDefault();
                recPanel.classList.toggle('hidden');
            });
        }

        // recommendations removed — no run button behavior
    })();
</script>
@endsection
