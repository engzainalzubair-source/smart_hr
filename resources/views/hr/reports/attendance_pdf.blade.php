<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} — Attendance Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; direction: ltr; }
        .header { text-align: center; margin-bottom: 10px; }
        .filters { font-size: 12px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        .small { font-size: 11px; color: #555; }
        .totals { margin-top: 10px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ config('app.name') }} — Attendance Report</h2>
        <div class="small">Generated at: {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</div>
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

    @php
        $col = collect($items ?? []);
        $total = $col->count();
        $present = $col->where('status','present')->count();
        $absent = $col->where('status','absent')->count();
        $late = $col->where('status','late')->count();
        $remote = $col->where('status','remote')->count();
    @endphp

    <div class="totals small">
        <div>Total Records: {{ $total }}</div>
        <div>Present: {{ $present }} &nbsp; | &nbsp; Absent: {{ $absent }} &nbsp; | &nbsp; Late: {{ $late }} &nbsp; | &nbsp; Remote: {{ $remote }}</div>
    </div>

    <table style="margin-top:8px">
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Employee</th>
                <th>Department</th>
                <th style="width:90px">Date</th>
                <th style="width:90px">Check In</th>
                <th style="width:90px">Check Out</th>
                <th style="width:80px">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $it)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ optional($it->employee)->first_name }} {{ optional($it->employee)->last_name }}</td>
                    <td>{{ optional(optional($it->employee)->department)->name }}</td>
                    <td>{{ $it->date }}</td>
                    <td>{{ $it->check_in }}</td>
                    <td>{{ $it->check_out }}</td>
                    <td>{{ ucfirst($it->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
