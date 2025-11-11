@php
    // Simple printable template uses LTR styles for English output
    $generated = $salary->generated_at ? $salary->generated_at->toDateString() : now()->toDateString();
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Salary Payment Slip</title>
    <style>
        body{font-family:DejaVu Sans, Arial, sans-serif;direction:ltr;padding:24px}
        .box{max-width:800px;margin:0 auto}
        table{width:100%;border-collapse:collapse;margin-top:12px}
        th,td{border:1px solid #ddd;padding:10px;text-align:left}
        th{background:#f3f4f6}
    </style>
</head>
<body>
    <div class="box">
        <h2 style="text-align:center">Salary Payment Slip</h2>
        <div style="text-align:left;margin-top:8px">Employee: {{ $salary->employee->first_name }} {{ $salary->employee->last_name }}</div>
        <div style="text-align:left">Period: {{ $salary->period_start }} - {{ $salary->period_end }}</div>
        <div style="text-align:left">Payment Date: {{ $generated }}</div>

        <table>
            <tr><th>Description</th><th>Amount</th></tr>
            <tr><td>Base Salary</td><td>{{ number_format($salary->base_salary,2) }}</td></tr>
            <tr><td>Bonuses</td><td>{{ number_format($salary->bonuses,2) }}</td></tr>
            <tr><td>Deductions</td><td>{{ number_format($salary->deductions,2) }}</td></tr>
            <tr><td>Adjustments</td><td>{{ number_format($salary->adjustments,2) }}</td></tr>
            <tr><th>Net Paid</th><th>{{ number_format($salary->net_pay,2) }}</th></tr>
        </table>

        <p style="margin-top:30px">Acknowledgement: I hereby acknowledge receipt of the above amount as salary for the specified period.</p>
        <p style="margin-top:40px">Employee Signature: ______________________</p>
    </div>
</body>
</html>
