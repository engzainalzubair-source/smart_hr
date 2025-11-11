<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <title>Departments Table</title>
    <style>
        /* Use a Latin-capable font and LTR direction for English PDF */
        body { font-family: 'DejaVu Sans', Arial, sans-serif; direction: ltr; }
        table { border-collapse: collapse; width: 100%; font-size: 12px; table-layout: auto; }
        th, td { border: 1px solid #444; padding: 6px; text-align: right; }
        th { background: #f3f4f6; }
        .number { text-align: right; }
        h2 { margin-bottom: 8px; }
        /* column sizing to keep numeric columns narrow */
        .col-name { width: auto; }
        .col-count { width: 60px; }
        .col-money { width: 110px; }
        .col-salary { width: 100px; }
        .col-percent { width: 70px; }
    </style>
</head>
<body>
    <h2>Departments — Statistical Table</h2>
    <table dir="ltr">
        <thead>
                <tr>
                <th class="col-name">Department</th>
                <th class="col-count">Employees</th>
                <th class="col-money">Total Salaries</th>
                <th class="col-salary">Avg Salary</th>
                <th class="col-salary">Max</th>
                <th class="col-salary">Min</th>
                <th class="col-percent">Avg Perf</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $dept)
                @php $s = $deptStats[$dept->id] ?? ['employees_count' => 0,'total_salary'=>0,'avg_salary'=>0,'max_salary'=>0,'min_salary'=>0,'avg_performance'=>0]; @endphp
                <tr>
                    <td class="col-name" dir="rtl">{{ $dept->name }}</td>
                    <td class="col-count number">{{ $s['employees_count'] }}</td>
                    <td class="col-money number">{{ number_format($s['total_salary'], 2) }}</td>
                    <td class="col-salary number">{{ number_format($s['avg_salary'], 2) }}</td>
                    <td class="col-salary number">{{ number_format($s['max_salary'], 2) }}</td>
                    <td class="col-salary number">{{ number_format($s['min_salary'], 2) }}</td>
                    <td class="col-percent number">{{ $s['avg_performance'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
