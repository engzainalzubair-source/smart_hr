<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تقرير الأداء</title>
    <style>
        body{font-family: DejaVu Sans, sans-serif; direction: rtl; color:#111}
        .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
        .title{font-size:20px;font-weight:700}
        .meta{font-size:12px;color:#555}
        table{width:100%;border-collapse:collapse;margin-top:8px}
        th,td{border:1px solid #ddd;padding:8px;text-align:right;font-size:12px}
        th{background:#f3f4f6}
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">تقرير الأداء المركب</div>
            <div class="meta">الفترة: آخر 90 يوم</div>
        </div>
        <div>
            <div class="meta">توليد: {{ now()->toDateTimeString() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الموظف</th>
                <th>متوسط الأداء</th>
                <th>نسبة الحضور</th>
                <th>الدرجة المركبة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topComposites as $i => $c)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $c['name'] }}</td>
                    <td>{{ $c['perfAvg'] }}%</td>
                    <td>{{ $c['attendanceRate'] }}%</td>
                    <td>{{ $c['composite'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($filters['q']) || !empty($filters['department_id']))
        <div style="margin-top:12px;font-size:12px;color:#333">
            <strong>فلاتر مطبقة:</strong>
            @if(!empty($filters['q'])) كَلمة: "{{ $filters['q'] }}" @endif
            @if(!empty($filters['department_id'])) - القسم: {{ optional(App\Models\Department::find($filters['department_id']))->name }} @endif
        </div>
    @endif

    @if(!empty($recommendations))
        <div style="margin-top:14px;font-size:12px;color:#222;">
            <h4 style="margin-bottom:6px">Recommendations (Top items)</h4>
            <table style="width:100%;border-collapse:collapse;margin-top:6px">
                <thead>
                    <tr>
                        <th style="border:1px solid #ddd;padding:6px;text-align:left">#</th>
                        <th style="border:1px solid #ddd;padding:6px;text-align:left">Employee</th>
                        <th style="border:1px solid #ddd;padding:6px;text-align:left">Issue</th>
                        <th style="border:1px solid #ddd;padding:6px;text-align:left">Suggestion</th>
                        <th style="border:1px solid #ddd;padding:6px;text-align:left">Confidence</th>
                    </tr>
                </thead>
                <tbody>
                @foreach(array_slice($recommendations,0,20) as $i => $r)
                    <tr>
                        <td style="border:1px solid #ddd;padding:6px">{{ $i+1 }}</td>
                        <td style="border:1px solid #ddd;padding:6px">{{ $r['name'] }}</td>
                        <td style="border:1px solid #ddd;padding:6px">{{ $r['issue'] }}</td>
                        <td style="border:1px solid #ddd;padding:6px">{{ $r['suggestion'] }}</td>
                        <td style="border:1px solid #ddd;padding:6px">{{ $r['confidence'] }}%</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</body>
</html>
