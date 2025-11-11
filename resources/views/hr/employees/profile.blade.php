@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- ====== بطاقة الملف الشخصي ====== -->
        <div class="col-span-1 bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
            <div class="text-center">
                <div class="h-28 w-28 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-full mx-auto flex items-center justify-center text-3xl font-bold shadow-md">
                    {{ strtoupper(substr($employee->first_name,0,1)) }}
                </div>
                <h3 class="mt-4 text-xl font-extrabold text-gray-800">{{ $employee->first_name }} {{ $employee->last_name }}</h3>
                <p class="text-sm text-indigo-600 font-medium">{{ $employee->job_title }}</p>
                <p class="text-sm text-gray-500">{{ $employee->department->name ?? '-' }}</p>
            </div>

            <div class="mt-6 space-y-3 text-sm text-gray-700">
                <div class="flex justify-between"><span class="font-semibold">📞 الهاتف:</span> <span id="emp_phone">{{ $employee->phone ?? '-' }}</span></div>
                <div>
                    <span class="font-semibold">🏠 العنوان:</span>
                    <div id="emp_address" class="text-gray-600 mt-1 bg-gray-50 p-2 rounded-md border">{!! nl2br(e($employee->address ?? '-')) !!}</div>
                </div>
                <div class="flex justify-between"><span class="font-semibold">📧 البريد:</span> <span>{{ $employee->email ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="font-semibold">📅 التوظيف:</span> <span>{{ $employee->hire_date ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="font-semibold">💰 الراتب:</span> <span>{{ $employee->salary ? number_format($employee->salary,2) : '-' }}</span></div>
                <div class="flex justify-between"><span class="font-semibold">⚙️ الحالة:</span>
                    <span class="px-2 py-1 text-xs rounded-full {{ $employee->status == 'active' ? 'bg-green-100 text-green-700' : ($employee->status == 'on_leave' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ $employee->status }}
                    </span>
                </div>
            </div>

            {{-- تحسين: لا تُظهر أزرار المودال هنا، سنعرض حقول قابلة للتعديل داخل تاب البيانات --}}
        </div>

        <!-- ====== بيانات الموظف وتبويبات ====== -->
        <div class="col-span-2 bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
            <nav class="mb-6 border-b border-gray-200">
                <ul class="flex flex-wrap gap-4 text-sm font-medium">
                    <li><a href="#tab-personal" class="tab-link text-indigo-600 border-b-2 border-indigo-600 pb-2" data-tab="personal">البيانات</a></li>
                    <li><a href="#tab-attendance" class="tab-link text-gray-600 hover:text-indigo-600" data-tab="attendance">الحضور</a></li>
                    <li><a href="#tab-performance" class="tab-link text-gray-600 hover:text-indigo-600" data-tab="performance">الأداء</a></li>
                    <li><a href="#tab-rewards" class="tab-link text-gray-600 hover:text-indigo-600" data-tab="rewards">المكافآت والخصومات</a></li>
                    <li><a href="#tab-leave" class="tab-link text-gray-600 hover:text-indigo-600" data-tab="leave">طلب إجازة</a></li>
                </ul>
            </nav>

            <!-- بيانات شخصية -->
            <div id="tab-personal" class="tab-content">
                <h4 class="font-bold mb-3 text-gray-800">البيانات الشخصية والوظيفية</h4>
                <form id="personalForm" class="space-y-4 bg-white p-4 rounded-lg border border-gray-100">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">الاسم الأول</label>
                            <input name="first_name" id="inp_first_name" value="{{ $employee->first_name }}" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">اسم العائلة</label>
                            <input name="last_name" id="inp_last_name" value="{{ $employee->last_name }}" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                            <input name="email" id="inp_email" value="{{ $employee->email }}" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">الهاتف</label>
                            <input name="phone" id="inp_phone" value="{{ $employee->phone }}" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">العنوان</label>
                            <textarea name="address" id="inp_address" class="mt-1 p-2 border rounded w-full" rows="3" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }}>{{ $employee->address }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">المسمى الوظيفي</label>
                            <input name="job_title" id="inp_job_title" value="{{ $employee->job_title }}" class="mt-1 p-2 border rounded w-full" disabled />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">القسم</label>
                            <input value="{{ $employee->department->name ?? '-' }}" class="mt-1 p-2 border rounded w-full bg-gray-50" disabled />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-3">
                        @if(isset($canEdit) && $canEdit)
                            <button id="savePersonalBtn" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded">حفظ التعديلات</button>
                            <span id="personalStatus" class="text-sm text-green-600 hidden">تم الحفظ</span>
                        @else
                            <div class="text-sm text-gray-500">غير مسموح بالتعديل.</div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- تبويب الحضور -->
            <div id="tab-attendance" class="tab-content hidden">
                <h4 class="font-bold mb-3 text-gray-800">سجل الحضور والانصراف (آخر {{ $attendances->count() }} سجل)</h4>
                <div class="overflow-auto max-h-80 rounded-lg border border-gray-100">
                    <table class="w-full text-sm text-gray-700">
                        <thead class="bg-gray-50 sticky top-0 border-b">
                            <tr>
                                <th class="p-2 text-right font-semibold">التاريخ</th>
                                <th class="p-2 text-right font-semibold">الحالة</th>
                                <th class="p-2 text-right font-semibold">من</th>
                                <th class="p-2 text-right font-semibold">إلى</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($attendances as $a)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-2">{{ $a->date->format('Y-m-d') ?? $a->date }}</td>
                                <td class="p-2">{{ $a->status }}</td>
                                <td class="p-2">{{ $a->time_in ?? '-' }}</td>
                                <td class="p-2">{{ $a->time_out ?? '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- تبويب الأداء -->
            <div id="tab-performance" class="tab-content hidden">
                <h4 class="font-bold mb-3 text-gray-800">تقارير الأداء (آخر {{ $performances->count() }})</h4>
                <div class="space-y-3">
                    @foreach($performances as $p)
                        <div class="p-4 bg-gray-50 border border-gray-100 rounded-lg hover:shadow-sm transition">
                            <div class="text-sm text-gray-500">{{ $p->date->format('Y-m-d') ?? $p->date }} — تقييم:
                                <span class="font-semibold text-indigo-700">{{ $p->score }}</span>
                            </div>
                            <div class="mt-1 text-gray-700">{{ $p->notes }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- تبويب المكافآت -->
            <div id="tab-rewards" class="tab-content hidden">
                <h4 class="font-bold mb-3 text-gray-800">المكافآت والخصومات (الأخيرة {{ $rewards->count() }})</h4>
                <div class="space-y-3">
                    @foreach($rewards as $r)
                        <div class="p-4 border border-gray-100 rounded-lg flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition">
                            <div>
                                <div class="text-sm text-gray-500">{{ $r->date->format('Y-m-d') ?? $r->date }} — نوع: <strong>{{ $r->type }}</strong></div>
                                <div class="mt-1 text-gray-700">{{ $r->notes }}</div>
                            </div>
                            <div class="font-semibold {{ $r->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $r->amount }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- تبويب طلب الاجازة الجديد -->
            <div id="tab-leave" class="tab-content hidden">
                <h4 class="font-bold mb-3 text-gray-800">طلب إجازة</h4>
                <form id="leaveForm" class="space-y-3 bg-white p-4 rounded-lg border border-gray-100">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium">من تاريخ</label>
                            <input type="date" name="start_date" id="leave_start" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">إلى تاريخ</label>
                            <input type="date" name="end_date" id="leave_end" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">نوع الإجازة</label>
                            <select name="type" id="leave_type" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }}>
                                <option value="annual">سنوية</option>
                                <option value="sick">مرضية</option>
                                <option value="unpaid">غير مدفوعة</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium">السبب / ملاحظات</label>
                            <textarea name="reason" id="leave_reason" rows="3" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }}></textarea>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if(isset($canEdit) && $canEdit)
                            <button id="submitLeaveBtn" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded">إرسال طلب الإجازة</button>
                            <span id="leaveStatus" class="text-sm text-green-600 hidden">تم إرسال الطلب</span>
                        @else
                            <div class="text-sm text-gray-500">غير مسموح بتقديم طلبات.</div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    // existing tab switching
    document.querySelectorAll('.tab-link').forEach(function(link){
        link.addEventListener('click', function(e){
            e.preventDefault();
            document.querySelectorAll('.tab-link').forEach(l=>l.classList.remove('border-b-2','pb-2','text-indigo-600'));
            link.classList.add('border-b-2','pb-2','text-indigo-600');
            const tab = link.dataset.tab;
            document.querySelectorAll('.tab-content').forEach(tc=>tc.classList.add('hidden'));
            document.getElementById('tab-'+tab).classList.remove('hidden');
        });
    });

    // Personal save
    const savePersonalBtn = document.getElementById('savePersonalBtn');
    if (savePersonalBtn) {
        savePersonalBtn.addEventListener('click', async function(){
            const btn = this; btn.disabled = true;
            const form = document.getElementById('personalForm');
            const data = new FormData();
            // add _method=PATCH for route
            data.append('_method','PATCH');
            ['first_name','last_name','email','phone','address'].forEach(k=> data.append(k, document.getElementById('inp_'+k.replace('_','_')) ? document.getElementById('inp_'+k.replace('_','_')).value : document.querySelector('[name="'+k+'"]').value ));
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            // determine action URL: employees.self.update route
            const action = '{{ route("employees.self.update", $employee) }}';
            try {
                const res = await fetch(action, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }, body: data, credentials: 'same-origin'});
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || res.statusText);
                document.getElementById('emp_phone').textContent = json.employee.phone || '-';
                document.getElementById('tab_phone').textContent = json.employee.phone || '-';
                document.getElementById('emp_address').innerHTML = (json.employee.address ? json.employee.address.replace(/\n/g,'<br/>') : '-');
                document.getElementById('tab_address').innerHTML = (json.employee.address ? json.employee.address.replace(/\n/g,'<br/>') : '-');
                const status = document.getElementById('personalStatus'); if (status) { status.classList.remove('hidden'); status.textContent='تم الحفظ'; setTimeout(()=>status.classList.add('hidden'),3000); }
            } catch (err) {
                alert('حدث خطأ عند حفظ البيانات: ' + err.message);
            } finally { btn.disabled = false; }
        });
    }

    // Leave submit
    const submitLeaveBtn = document.getElementById('submitLeaveBtn');
    if (submitLeaveBtn) {
        submitLeaveBtn.addEventListener('click', async function(){
            const btn = this; btn.disabled = true;
            const data = new FormData(document.getElementById('leaveForm'));
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const action = '{{ route("employees.leave.submit", $employee) }}';
            try {
                const res = await fetch(action, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }, body: data, credentials: 'same-origin' });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || res.statusText);
                const status = document.getElementById('leaveStatus'); if (status) { status.classList.remove('hidden'); status.textContent = 'تم إرسال الطلب'; setTimeout(()=>status.classList.add('hidden'),4000); }
                document.getElementById('leaveForm').reset();
            } catch (err) {
                alert('حدث خطأ عند إرسال طلب الإجازة: ' + err.message);
            } finally { btn.disabled = false; }
        });
    }
})();
</script>
@endsection
