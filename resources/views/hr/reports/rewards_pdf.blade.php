<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rewards and Penalties Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; direction: ltr; }
        .header { text-align: center; margin-bottom: 10px; }
        .filters { font-size: 11px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f4f4f4; }
        .small { font-size: 11px; color: #555; }
        .totals { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Rewards & Penalties Report</h2>
        <div class="small">Generated at: {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</div>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Filters:</strong>
        @foreach($filters as $k=>$v)
            @if($v)
                <span class="small">{{ ucfirst($k) }}: {{ $v }}</span>
            @endif
        @endforeach
    </div>
    @endif

    <div class="totals small">
        <div>Total Rewards: {{ number_format(optional($totals->get('reward'))->total ?? 0,2) }}</div>
        <div>Total Penalties: {{ number_format(optional($totals->get('penalty'))->total ?? 0,2) }}</div>
        <div>Net: {{ number_format((optional($totals->get('reward'))->total ?? 0) - (optional($totals->get('penalty'))->total ?? 0),2) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Reason</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $it)
            <tr>
                <td>{{ $it->id }}</td>
                <td>{{ optional($it->employee)->first_name }} {{ optional($it->employee)->last_name }}</td>
                <td>{{ optional(optional($it->employee)->department)->name }}</td>
                <td>{{ ucfirst($it->type) }}</td>
                <td>{{ $it->value_type=='percentage' ? $it->amount . '%' : number_format($it->amount,2) }}</td>
                <td>{{ $it->reason }}</td>
                <td>{{ $it->issued_at ? $it->issued_at->format('Y-m-d') : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:10px" class="small">
        <div>Records: {{ count($items) }}</div>
    </div>
</body>
</html>
