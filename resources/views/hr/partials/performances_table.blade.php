<div class="bg-white p-6 rounded-xl shadow-lg">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-800">تقارير الأداء</h2>
            <p class="text-sm text-gray-500">تحليل درجات الأداء المركبة، التوصيات والدورات المقترحة. يمكنك تصدير التقرير أو طباعته بسهولة.</p>
        </div>

        <!-- Filters & Actions -->
        <div class="flex flex-wrap items-center gap-2">
            <form id="perfFilterForm" method="GET" action="{{ route('hr.performances.index') }}" class="flex items-center gap-2 flex-wrap">
                <input name="q" id="perfSearch" type="search" value="{{ request('q') }}" placeholder="بحث باسم الموظف..." class="px-3 py-2 border rounded-md text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" />
                <select name="department_id" class="px-3 py-2 border rounded-md text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">كل الأقسام</option>
                    @foreach($departments ?? [] as $dep)
                        <option value="{{ $dep->id }}" {{ request('department_id') == $dep->id ? 'selected' : '' }}>{{ $dep->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 transition">تصفية</button>
            </form>

            @php $exportUrl = route('hr.performances.exportPdf') . (request()->getQueryString() ? ('?' . request()->getQueryString()) : ''); @endphp
            <a href="{{ $exportUrl }}" target="_blank" id="exportPdfBtn" class="px-3 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700 transition">تصدير PDF</a>
        </div>
    </div>

    <!-- Main Grid -->
    <div id="perfReport" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left / Main Section -->
        <div class="col-span-2 space-y-4">
            <!-- Chart Section -->
            <div>
                <h3 class="text-md font-semibold mb-1 text-gray-700">أداء مركب (آخر {{ 90 }} يوم)</h3>
                <p class="text-sm text-gray-500 mb-2">الدرجة المركبة تجمع الأداء والتزام الحضور لتصنيف الموظفين.</p>
                <div class="relative h-56 bg-white p-3 rounded shadow-sm border">
                    <canvas id="chartComposite"></canvas>
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto mt-4 border rounded-lg shadow-sm">
                <table id="perfTable" class="w-full text-right table-auto border-collapse">
                    <thead class="bg-gray-50 text-gray-600 text-sm">
                        <tr>
                            <th class="p-3 border">#</th>
                            <th class="p-3 border">الموظف</th>
                            <th class="p-3 border">متوسط الأداء</th>
                            <th class="p-3 border">نسبة الحضور</th>
                            <th class="p-3 border">الدرجة المركبة</th>
                            <th class="p-3 border">توصية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topComposites as $i => $c)
                                @php
                                    // Lookup a generated recommendation for this employee if available
                                    $empId = $c['employee_id'] ?? null;
                                    $recObj = ($empId && !empty($recommendationsById[$empId])) ? $recommendationsById[$empId] : null;
                                    // Fallback short recommendations if service didn't provide one
                                    if ($recObj) {
                                        $shortRec = $recObj['suggestion'];
                                        $confidence = $recObj['confidence'] ?? null;
                                    } else {
                                        if ($c['composite'] >= 85) {
                                            $shortRec = 'Consider stretch assignments or promotion path.';
                                        } elseif ($c['perfAvg'] < 60 && $c['attendanceRate'] >= 70) {
                                            $shortRec = 'Assign targeted skills training and pair with a mentor.';
                                        } elseif ($c['attendanceRate'] < 60) {
                                            $shortRec = 'Investigate attendance issues and assign improvement plan.';
                                        } elseif ($c['composite'] >= 70) {
                                            $shortRec = 'Recommend advanced upskilling courses (leadership/time management).';
                                        } else {
                                            $shortRec = 'Recommend foundational training and regular coaching.';
                                        }
                                        $confidence = null;
                                    }
                                @endphp
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3 text-sm">{{ $i + 1 }}</td>
                                    <td class="p-3 text-sm">{{ $c['name'] }}</td>
                                    <td class="p-3 text-sm">{{ $c['perfAvg'] }}%</td>
                                    <td class="p-3 text-sm">{{ $c['attendanceRate'] }}%</td>
                                    <td class="p-3 text-sm font-semibold text-indigo-600">{{ $c['composite'] }}%</td>
                                    <td class="p-3 text-sm text-gray-700">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex-1">{{ Str::limit($shortRec, 120) }}</div>
                                            @if($confidence)
                                                <div class="text-xs text-gray-500">{{ $confidence }}%</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right / Sidebar Section -->
        <div class="space-y-4">
            {{-- Render dynamically generated recommendations --}}
            @include('hr.partials.performance_recommendations', ['recommendations' => $recommendations ?? []])
            <div class="p-4 border rounded shadow-sm bg-gray-50">
                <h4 class="font-semibold mb-2 text-gray-700">Suggested Courses</h4>
                <ol class="list-decimal list-inside text-sm space-y-2 text-gray-600">
                    <li>Communication & Collaboration</li>
                    <li>Time Management & Productivity</li>
                    <li>Team Leadership & Coaching</li>
                    <li>Role-specific Technical Upskilling</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">{{ $performances->links() ?? '' }}</div>
</div>

<!-- Scripts -->
<script>
(function(){
    const data = {!! json_encode($topComposites ?? []) !!};

    // Search filter
    const search = document.getElementById('perfSearch');
    if (search) {
        search.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase().trim();
            document.querySelectorAll('#perfTable tbody tr').forEach(tr => {
                const name = tr.children[1].textContent.toLowerCase();
                tr.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }

    // Export CSV
    function exportCsv() {
        const rows = [['#','Employee','PerfAvg','AttendanceRate','Composite','Recommendation']];
        document.querySelectorAll('#perfTable tbody tr').forEach(tr => {
            if (tr.style.display === 'none') return;
            const cols = Array.from(tr.children).map(td => td.textContent.trim().replace(/,/g,''));
            rows.push(cols);
        });
        const csv = rows.map(r=>r.join(',')).join('\n');
        const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = 'performance_report.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }
    document.getElementById('exportCsvBtn')?.addEventListener('click', exportCsv);

    // Print
    document.getElementById('printBtn')?.addEventListener('click', () => {
        const el = document.getElementById('perfReport');
        const w = window.open('', '_blank');
        const style = `<style>
            body{font-family:sans-serif;direction:rtl;}
            table{width:100%;border-collapse:collapse;}
            td,th{border:1px solid #ddd;padding:8px;text-align:right;}
            th{background:#f3f4f6}
        </style>`;
        w.document.write('<html><head><title>تقرير الأداء</title>'+style+'</head><body>');
        w.document.write(el.innerHTML);
        w.document.write('</body></html>');
        w.document.close();
        setTimeout(()=>{ w.print(); w.close(); },300);
    });
})();
</script>
