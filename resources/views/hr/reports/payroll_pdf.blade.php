<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Report</title>
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
        <h2>Payroll Report</h2>
        <div class="small">Period: {{ $year }} / {{ $month }} &nbsp; &nbsp; Generated at: {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</div>
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
        $col = collect($rows ?? []);
        $total = $col->count();
        $sumBase = $col->sum('base_salary');
        $sumBonuses = $col->sum('bonuses');
        $sumDeductions = $col->sum('deductions');
        $sumFinal = $col->sum(function($r){ return $r['final_salary'] ?? $r['net_pay']; });
    @endphp

    <div class="totals small">
        <div>Total Employees: {{ $total }}</div>
        <div>Base Total: {{ number_format($sumBase,2) }} &nbsp; | &nbsp; Bonuses: {{ number_format($sumBonuses,2) }} &nbsp; | &nbsp; Deductions: {{ number_format($sumDeductions,2) }} &nbsp; | &nbsp; Net/Final Total: {{ number_format($sumFinal,2) }}</div>
    </div>

    <table style="margin-top:8px">
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Employee</th>
                <th>Department</th>
                <th style="width:90px">Base Salary</th>
                <th style="width:90px">Bonuses</th>
                <th style="width:90px">Deductions</th>
                <th style="width:80px">Absent Days</th>
                <th style="width:100px">Absent (w/o reason)</th>
                <th style="width:100px">Absent (with reason)</th>
                <th style="width:100px">Absent Deduction</th>
                <th style="width:100px">Final Salary</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $r)
                @php $emp = $r['employee']; $dept = $emp->department->name ?? ''; @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $emp->first_name }} {{ $emp->last_name }}</td>
                    <td>{{ $dept }}</td>
                    <td>{{ number_format($r['base_salary'] ?? 0,2) }}</td>
                    <td>{{ number_format($r['bonuses'] ?? 0,2) }}</td>
                    <td>{{ number_format($r['deductions'] ?? 0,2) }}</td>
                    <td>{{ $r['absence_count'] ?? 0 }}</td>
                    <td>{{ $r['absent_without_reason'] ?? 0 }}</td>
                    <td>{{ $r['absent_with_reason'] ?? 0 }}</td>
                    <td>{{ number_format($r['absence_deduction'] ?? 0,2) }}</td>
                    <td>{{ number_format($r['final_salary'] ?? $r['net_pay'] ?? 0,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
