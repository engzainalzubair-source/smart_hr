@php use App\Models\Employee; @endphp

@if(config('app.debug'))
    <div class="mb-2 p-2 bg-gray-50 text-xs text-gray-700">
        <div class="font-medium">DEBUG: raw data</div>
        <pre style="max-height:200px;overflow:auto">Suggestions: {{ json_encode($suggestions, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
        <pre style="max-height:200px;overflow:auto">Diagnostics: {{ json_encode($diagnostics ?? null, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endif

@if(empty($suggestions) || count($suggestions) == 0)
    <div class="text-sm text-gray-500">لا توجد توصيات حالية للفترة المحددة.</div>
    @if(!empty($diagnostics))
        <div class="mt-2 p-3 bg-yellow-50 border-l-4 border-yellow-300 text-xs text-gray-700">
            <div class="font-medium">ملاحظات توضيحية:</div>
            <ul class="list-disc list-inside mt-1">
                @if(isset($diagnostics['error']))
                    <li>خطأ أثناء إنشاء التوصيات: {{ $diagnostics['error'] }}</li>
                @endif
                @if(isset($diagnostics['employees']))
                    <li>عدد الموظفين في النظام: {{ $diagnostics['employees'] }}</li>
                @endif
                @if(isset($diagnostics['attendance_rows']))
                    <li>سجلات الحضور في الفترة {{ $diagnostics['period'][0] }} — {{ $diagnostics['period'][1] }}: {{ $diagnostics['attendance_rows'] }}</li>
                @endif
                @if(isset($diagnostics['performance_rows']))
                    <li>سجلات الأداء في الفترة {{ $diagnostics['period'][0] }} — {{ $diagnostics['period'][1] }}: {{ $diagnostics['performance_rows'] }}</li>
                @endif
                @if(isset($diagnostics['diag_error']))
                    <li>خطأ في جمع التشخيص: {{ $diagnostics['diag_error'] }}</li>
                @endif
            </ul>
            <div class="mt-2 text-xs text-gray-600">ملاحظة: التوصيات تتطلب بيانات حضور وأداء كافية لتوليد اقتراحات ذات ثقة عالية.</div>
        </div>
    @endif
@else
    <div class="space-y-4">
        @foreach($suggestions as $s)
            @php $emp = Employee::find($s['employee_id']); @endphp
            <div class="border rounded p-3 bg-white shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold">{{ $emp ? $emp->first_name . ' ' . $emp->last_name : 'الموظف #' . $s['employee_id'] }}</div>
                        <div class="text-xs text-gray-500">{{ $emp ? $emp->email : '' }} • {{ $emp && $emp->department ? $emp->department->name : '' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm">نوع التوصية: <span class="font-semibold">{{ ucfirst($s['type']) }}</span></div>
                        <div class="text-xs">نقاط: <span class="font-medium">{{ $s['score'] }}</span></div>
                        <div class="mt-1">
                            <span class="inline-block px-2 py-1 text-xs rounded {{ $s['confidence'] >= 0.7 ? 'bg-green-100 text-green-800' : ($s['confidence'] >= 0.4 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">ثقة: {{ intval($s['confidence'] * 100) }}%</span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-gray-600">المبلغ المقترح</div>
                        <div class="text-lg font-semibold">{{ number_format($s['suggested_amount'] ?? 0,2) }}</div>
                        <div class="text-xs text-gray-500">الإجراء الموصى به: {{ $s['suggested_action'] ?? '-' }}</div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs text-gray-600">مبررات وآليات التوصية</div>
                        <ul class="list-disc list-inside text-xs text-gray-700 mt-1">
                            @foreach($s['rationale'] ?? [] as $r)
                                <li>{{ $r }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="mt-3 flex items-center gap-2">
                    <form method="POST" action="{{ route('hr.rewards.store') }}" class="inline">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $s['employee_id'] }}" />
                        <input type="hidden" name="type" value="{{ $s['type'] }}" />
                        <input type="hidden" name="amount" value="{{ $s['suggested_amount'] }}" />
                        <input type="hidden" name="reason" value="{{ $s['reason'] ?? 'Auto recommendation' }} (auto)" />
                        <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded text-sm">تطبيق</button>
                    </form>
                    <button onclick="document.getElementById('meta-{{ $s['employee_id'] }}').classList.toggle('hidden')" class="px-3 py-1 bg-gray-200 rounded text-sm">تفاصيل البيانات</button>
                    <div id="meta-{{ $s['employee_id'] }}" class="hidden text-xs text-gray-700 bg-gray-50 p-2 rounded mt-2 w-full">
                        <pre>{{ json_encode($s['metadata'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
