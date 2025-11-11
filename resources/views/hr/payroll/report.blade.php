@php
    // English payroll report
    function fmt($v){ return number_format($v,2); }
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Report - {{ $year }}-{{ $month }}</title>
    <style>
        body{font-family:DejaVu Sans, Arial, sans-serif}
        table{width:100%;border-collapse:collapse}
        th,td{border:1px solid #ddd;padding:8px;text-align:left}
        th{background:#f3f4f6}
    </style>
</head>
<body>
    <h2>Payroll Report - {{ $year }} / {{ $month }}</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Base Salary</th>
                <th>Bonuses</th>
                <th>Deductions</th>
                <th>Absent Days</th>
                <th>Absent Deduction</th>
                <th>Final Salary</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $r)
                @php $emp = $r['employee']; $dept = $emp->department->name ?? ''; @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $emp->first_name }} {{ $emp->last_name }}</td>
                    <td>{{ $dept }}</td>
                    <td>{{ fmt($r['base_salary']) }}</td>
                    <td>{{ fmt($r['bonuses']) }}</td>
                    <td>{{ fmt($r['deductions']) }}</td>
                    <td>{{ $r['absence_count'] ?? 0 }}</td>
                    <td>{{ fmt($r['absence_deduction'] ?? 0) }}</td>
                    <td>{{ fmt($r['final_salary'] ?? $r['net_pay']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
