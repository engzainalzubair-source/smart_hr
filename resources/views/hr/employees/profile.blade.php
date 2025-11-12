@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- ====== Profile Card ====== -->
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
                <div class="flex justify-between"><span class="font-semibold">📞 Phone:</span> <span id="emp_phone">{{ $employee->phone ?? '-' }}</span></div>
                <div>
                    <span class="font-semibold">🏠 Address:</span>
                    <div id="emp_address" class="text-gray-600 mt-1 bg-gray-50 p-2 rounded-md border">{!! nl2br(e($employee->address ?? '-')) !!}</div>
                </div>
                <div class="flex justify-between"><span class="font-semibold">📧 Email:</span> <span>{{ $employee->email ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="font-semibold">📅 Hire Date:</span> <span>{{ $employee->hire_date ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="font-semibold">💰 Salary:</span> <span>{{ $employee->salary ? number_format($employee->salary,2) : '-' }}</span></div>
                <div class="flex justify-between"><span class="font-semibold">⚙️ Status:</span>
                    <span class="px-2 py-1 text-xs rounded-full {{ $employee->status == 'active' ? 'bg-green-100 text-green-700' : ($employee->status == 'on_leave' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ $employee->status }}
                    </span>
                </div>
            </div>

            {{-- improvement: do not show modal buttons here; fields are editable inside the data tab --}}
        </div>

        <!-- ====== Employee Data & Tabs ====== -->
        <div class="col-span-2 bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
            <nav class="mb-6 border-b border-gray-200">
                <ul class="flex flex-wrap gap-4 text-sm font-medium">
                    <li><a href="#tab-personal" class="tab-link text-indigo-600 border-b-2 border-indigo-600 pb-2" data-tab="personal">Personal</a></li>
                    <li><a href="#tab-attendance" class="tab-link text-gray-600 hover:text-indigo-600" data-tab="attendance">Attendance</a></li>
                    <li><a href="#tab-performance" class="tab-link text-gray-600 hover:text-indigo-600" data-tab="performance">Performance</a></li>
                    <li><a href="#tab-rewards" class="tab-link text-gray-600 hover:text-indigo-600" data-tab="rewards">Rewards & Deductions</a></li>
                    <li><a href="#tab-leave" class="tab-link text-gray-600 hover:text-indigo-600" data-tab="leave">Request Leave</a></li>
                </ul>
            </nav>

            <!-- Personal Data -->
            <div id="tab-personal" class="tab-content">
                <h4 class="font-bold mb-3 text-gray-800">Personal & Job Details</h4>
                <form id="personalForm" class="space-y-4 bg-white p-4 rounded-lg border border-gray-100">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                            <input name="first_name" id="inp_first_name" value="{{ $employee->first_name }}" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input name="last_name" id="inp_last_name" value="{{ $employee->last_name }}" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input name="email" id="inp_email" value="{{ $employee->email }}" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input name="phone" id="inp_phone" value="{{ $employee->phone }}" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea name="address" id="inp_address" class="mt-1 p-2 border rounded w-full" rows="3" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }}>{{ $employee->address }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Job Title</label>
                            <input name="job_title" id="inp_job_title" value="{{ $employee->job_title }}" class="mt-1 p-2 border rounded w-full" disabled />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Department</label>
                            <input value="{{ $employee->department->name ?? '-' }}" class="mt-1 p-2 border rounded w-full bg-gray-50" disabled />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-3">
                        @if(isset($canEdit) && $canEdit)
                            <button id="savePersonalBtn" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded">Save Changes</button>
                            <span id="personalStatus" class="text-sm text-green-600 hidden">Saved</span>
                        @else
                            <div class="text-sm text-gray-500">Editing not allowed.</div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Attendance Tab -->
            <div id="tab-attendance" class="tab-content hidden">
                <h4 class="font-bold mb-3 text-gray-800">Attendance Records (Last {{ $attendances->count() }})</h4>
                <div class="overflow-auto max-h-80 rounded-lg border border-gray-100">
                    <table class="w-full text-sm text-gray-700">
                        <thead class="bg-gray-50 sticky top-0 border-b">
                            <tr>
                                <th class="p-2 text-right font-semibold">Date</th>
                                <th class="p-2 text-right font-semibold">Status</th>
                                <th class="p-2 text-right font-semibold">From</th>
                                <th class="p-2 text-right font-semibold">To</th>
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

            <!-- Performance Tab -->
            <div id="tab-performance" class="tab-content hidden">
                <h4 class="font-bold mb-3 text-gray-800">Performance Reports (Last {{ $performances->count() }})</h4>
                <div class="space-y-3">
                    @foreach($performances as $p)
                        <div class="p-4 bg-gray-50 border border-gray-100 rounded-lg hover:shadow-sm transition">
                            <div class="text-sm text-gray-500">{{ $p->date->format('Y-m-d') ?? $p->date }} — Score:
                                <span class="font-semibold text-indigo-700">{{ $p->score }}</span>
                            </div>
                            <div class="mt-1 text-gray-700">{{ $p->notes }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Rewards Tab -->
            <div id="tab-rewards" class="tab-content hidden">
                <h4 class="font-bold mb-3 text-gray-800">Rewards & Deductions (Last {{ $rewards->count() }})</h4>
                <div class="space-y-3">
                    @foreach($rewards as $r)
                        <div class="p-4 border border-gray-100 rounded-lg flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition">
                            <div>
                                <div class="text-sm text-gray-500">{{ $r->date->format('Y-m-d') ?? $r->date }} — Type: <strong>{{ $r->type }}</strong></div>
                                <div class="mt-1 text-gray-700">{{ $r->notes }}</div>
                            </div>
                            <div class="font-semibold {{ $r->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $r->amount }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Leave Request Tab -->
            <div id="tab-leave" class="tab-content hidden">
                <h4 class="font-bold mb-3 text-gray-800">Request Leave</h4>
                <form id="leaveForm" class="space-y-3 bg-white p-4 rounded-lg border border-gray-100">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium">From Date</label>
                            <input type="date" name="start_date" id="leave_start" lang="en" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">To Date</label>
                            <input type="date" name="end_date" id="leave_end" lang="en" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }} />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Leave Type</label>
                            <select name="type" id="leave_type" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }}>
                                <option value="annual">Annual</option>
                                <option value="sick">Sick</option>
                                <option value="unpaid">Unpaid</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium">Reason / Notes</label>
                            <textarea name="reason" id="leave_reason" rows="3" class="mt-1 p-2 border rounded w-full" {{ isset($canEdit) && $canEdit ? '' : 'disabled' }}></textarea>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if(isset($canEdit) && $canEdit)
                            <button id="submitLeaveBtn" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded">Submit Leave Request</button>
                            <span id="leaveStatus" class="text-sm text-green-600 hidden">Request Sent</span>
                        @else
                            <div class="text-sm text-gray-500">Not allowed to submit requests.</div>
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
            const el = document.getElementById('tab-'+tab);
            if (el) el.classList.remove('hidden');
        });
    });

    // Personal save
    const savePersonalBtn = document.getElementById('savePersonalBtn');
    if (savePersonalBtn) {
        savePersonalBtn.addEventListener('click', async function(){
            const btn = this; btn.disabled = true;
            const data = new FormData();
            data.append('_method','PATCH');
            ['first_name','last_name','email','phone','address'].forEach(k=>{
                const el = document.getElementById('inp_'+k) || document.querySelector('[name="'+k+'"]');
                data.append(k, el ? el.value : '');
            });
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
            const action = '{{ route("employees.self.update", $employee) }}';
            try {
                const res = await fetch(action, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }, body: data, credentials: 'same-origin'});
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || res.statusText);
                const empPhoneEl = document.getElementById('emp_phone');
                if (empPhoneEl) empPhoneEl.textContent = json.employee.phone || '-';
                const tabPhoneEl = document.getElementById('tab_phone');
                if (tabPhoneEl) tabPhoneEl.textContent = json.employee.phone || '-';
                const empAddrEl = document.getElementById('emp_address');
                if (empAddrEl) empAddrEl.innerHTML = (json.employee.address ? json.employee.address.replace(/\n/g,'<br/>') : '-');
                const tabAddrEl = document.getElementById('tab_address');
                if (tabAddrEl) tabAddrEl.innerHTML = (json.employee.address ? json.employee.address.replace(/\n/g,'<br/>') : '-');
                const status = document.getElementById('personalStatus'); if (status) { status.classList.remove('hidden'); status.textContent='Saved'; setTimeout(()=>status.classList.add('hidden'),3000); }
            } catch (err) {
                alert('An error occurred while saving: ' + (err.message || err));
            } finally { btn.disabled = false; }
        });
    }

    // Leave submit
    const submitLeaveBtn = document.getElementById('submitLeaveBtn');
    if (submitLeaveBtn) {
        submitLeaveBtn.addEventListener('click', async function(){
            const btn = this; btn.disabled = true;
            const data = new FormData(document.getElementById('leaveForm'));
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
            const action = '{{ route("employees.leave.submit", $employee) }}';
            try {
                const res = await fetch(action, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }, body: data, credentials: 'same-origin' });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || res.statusText);
                const status = document.getElementById('leaveStatus'); if (status) { status.classList.remove('hidden'); status.textContent = 'Request Sent'; setTimeout(()=>status.classList.add('hidden'),4000); }
                const leaveForm = document.getElementById('leaveForm');
                if (leaveForm) leaveForm.reset();
            } catch (err) {
                alert('An error occurred while submitting the leave request: ' + (err.message || err));
            } finally { btn.disabled = false; }
        });
    }
})();
</script>
@endsection
