@php
    // Simple printable template uses same RTL friendly styles as other reports
    $generated = $salary->generated_at ? $salary->generated_at->toDateString() : now()->toDateString();
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>سند صرف راتب</title>
    <style>
        body{font-family:DejaVu Sans, Arial, sans-serif;direction:rtl;padding:24px}
        .box{max-width:800px;margin:0 auto}
        table{width:100%;border-collapse:collapse;margin-top:12px}
        th,td{border:1px solid #ddd;padding:10px;text-align:right}
        th{background:#f3f4f6}
    </style>
</head>
<body>
    <div class="box">
        <h2 style="text-align:center">سند صرف راتب</h2>
        <div style="text-align:right;margin-top:8px">الموظف: {{ $salary->employee->first_name }} {{ $salary->employee->last_name }}</div>
        <div style="text-align:right">الفترة: {{ $salary->period_start }} - {{ $salary->period_end }}</div>
        <div style="text-align:right">تاريخ الصرف: {{ $generated }}</div>

        <table>
            <tr><th>الوصف</th><th>المبلغ</th></tr>
            <tr><td>الراتب الأساسي</td><td>{{ number_format($salary->base_salary,2) }}</td></tr>
            <tr><td>المكافآت</td><td>{{ number_format($salary->bonuses,2) }}</td></tr>
            <tr><td>الخصومات</td><td>{{ number_format($salary->deductions,2) }}</td></tr>
            <tr><td>تعديلات</td><td>{{ number_format($salary->adjustments,2) }}</td></tr>
            <tr><th>الصافي المدفوع</th><th>{{ number_format($salary->net_pay,2) }}</th></tr>
        </table>

        <p style="margin-top:30px">إقرار: باستلامي هذا المبلغ أعلاه كراتب عن الفترة المحددة.</p>
        <p style="margin-top:40px">توقيع الموظف: ______________________</p>
    </div>
</body>
</html>
