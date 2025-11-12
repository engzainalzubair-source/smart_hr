<div class="bg-white p-4 rounded shadow">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
        <div>
            <h2 class="text-lg font-semibold">Attendance Register</h2>
            <p class="text-sm text-gray-500">Mark attendance quickly, search staff, and export reports.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('hr.attendances.index') }}" class="inline-flex items-center">
                <input type="date" name="date" value="{{ $date ?? now()->toDateString() }}" lang="en"class="border px-2 py-1 rounded text-sm" />
                <input name="q" type="search" value="{{ request('q') }}" placeholder="Search name..." class="px-2 py-1 border rounded text-sm" />
                <select name="per_page" class="border px-2 py-1 rounded text-sm">
                    <option value="25" {{ request('per_page')==25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page')==50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page')==100 ? 'selected' : '' }}>100</option>
                </select>
                <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded">Filter</button>
            </form>

            @php $exportUrl = route('hr.attendances.exportPdf') . (request()->getQueryString() ? ('?' . request()->getQueryString()) : '?'); @endphp
            <a href="{{ $exportUrl }}" target="_blank" class="px-3 py-1 bg-red-600 text-white rounded text-sm">Export PDF</a>
        </div>
    </div>
    <!-- Today's attendance switches -->
    <div class="mt-4">
        <div class="mb-2 flex items-center justify-between">
            <h3 class="font-medium  ">Staff (Paginated)</h3>
            <div class="flex items-center gap-2">
                <button id="selectAllBtn" class="px-1 py-2 border  rounded text-sm">Select All on Page</button>
                <form id="bulkForm" method="POST" action="{{ route('hr.attendances.bulkMark') }}" class="inline-flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date ?? now()->toDateString() }}" />
                    <input type="hidden" name="employee_ids" id="employee_ids_input" />
                    <label class="flex items-center gap-1 text-sm">Time
                        <input type="time" name="check_in" step="1" class="border px-1 py-1 rounded text-sm time-input" placeholder="HH:MM:SS" />
                        <button type="button" class="now-btn px-2 py-1 text-xs bg-gray-200 rounded ml-1" title="Set current time">Now</button>
                    </label>
                    <label class="flex items-center gap-1 text-sm">Out
                        <input type="time" name="check_out" step="1" class="border px-1 py-1 rounded text-sm time-input" placeholder="HH:MM:SS" />
                        <button type="button" class="now-btn px-2 py-1 text-xs bg-gray-200 rounded ml-1" title="Set current time">Now</button>
                    </label>
                    <button type="button" data-status="present" class=" px-2 py-2 bg-green-600 text-white rounded text-sm">Mark Present</button>
                    <button type="button" data-status="absent" class=" px-2 py-2 bg-gray-600 text-white rounded text-sm">Mark Absent</button>
                    <button type="button" data-status="checkout" class="px-2 py-2 bg-blue-600 text-white rounded text-sm">Mark Check-out</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1  gap-2">
            @foreach($employees as $emp)
                <div class="flex items-center justify-between p-2 border rounded">
                    <div>
                        <div class="">{{ $emp->first_name }} {{ $emp->last_name }}</div>
                        <div class="text-sm text-gray-500">{{ optional($emp->department)->name }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="employee-checkbox" value="{{ $emp->id }}">
                            <span class="text-sm">Select</span>
                        </label>
                        <form method="POST" action="{{ route('hr.attendances.mark') }}" class="attendance-mark inline-flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                            @php $att = optional($todayAttendances->get($emp->id)); @endphp
                            <select name="status" class="border rounded px-2 py-1 text-sm">
                                <option value="present" {{ $att && $att->status=='present' ? 'selected' : '' }}>Present</option>
                                <option value="absent" {{ $att && $att->status=='absent' ? 'selected' : '' }}>Absent</option>
                                <option value="leave" {{ $att && $att->status=='leave' ? 'selected' : '' }}>Leave</option>
                                <option value="late" {{ $att && $att->status=='late' ? 'selected' : '' }}>Late</option>
                            </select>
                            <div class="flex items-center gap-1">
                                <input type="time" name="check_in" step="1"
                                    value="{{ $att && $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i:s') : '' }}"
                                    class="border px-1 py-1 rounded text-sm time-input" />
                                <button type="button" class="now-btn px-2 py-1 text-xs bg-gray-200 rounded" title="Set current time">Now</button>
                            </div>
                            <div class="flex items-center gap-1">
                                <input type="time" name="check_out" step="1"
                                    value="{{ $att && $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i:s') : '' }}"
                                    class="border px-1 py-1 rounded text-sm time-input" />
                                <button type="button" class="now-btn px-2 py-1 text-xs bg-gray-200 rounded" title="Set current time">Now</button>
                            </div>
                            <button type="submit" class="px-2 py-1 bg-blue-600 text-white rounded text-sm">Save</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent attendance records -->
    <div class="mt-6">
        <table class="w-full text-right">
            <thead>
                <tr class="bg-gray-50">
                    <th class="p-2">#</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $a)
                    <tr class="border-t">
                        <td class="p-2">{{ $a->id }}</td>
                        <td>{{ optional($a->employee)->first_name }} {{ optional($a->employee)->last_name }}</td>
                        <td>{{ $a->date }}</td>
                        <td>{{ $a->check_in }}</td>
                        <td>{{ $a->check_out }}</td>
                        <td>{{ $a->status }}</td>
                        <td>
                            <form method="GET" action="{{ route('hr.attendances.edit', $a) }}" class="inline-fetch inline-block" data-tab="attendance" data-inline="true">
                                <input type="hidden" name="inline" value="1" />
                                <button type="submit" class="text-blue-600">Edit</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <div class="mb-2">Showing recent attendance records for {{ $date ?? now()->toDateString() }}</div>
        {{ $attendances->links() ?? '' }}
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const selectAllBtn = document.getElementById('selectAllBtn');
        const checkboxes = Array.from(document.querySelectorAll('.employee-checkbox'));
        selectAllBtn && selectAllBtn.addEventListener('click', function(){
            const allChecked = checkboxes.every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
        });

        // helper: format a JS Date to "HH:MM"
        function nowHHMM() {
            const now = new Date();
            const hh = String(now.getHours()).padStart(2,'0');
            const mm = String(now.getMinutes()).padStart(2,'0');
            return `${hh}:${mm}`;
        }

        // helper: normalize time string to "HH:MM" (drops seconds if present)
        function normalizeToHHMM(val) {
            if (!val) return '';
            const parts = val.split(':');
            if (parts.length >= 2) return `${parts[0].padStart(2,'0')}:${parts[1].padStart(2,'0')}`;
            return val;
        }

        // "Now" buttons helper - sets the nearest time-input to current time (HH:MM)
        document.querySelectorAll('.now-btn').forEach(btn => {
            btn.addEventListener('click', function(){
                const container = this.closest('label, div, form') || document;
                const input = container.querySelector('.time-input');
                if (!input) return;
                input.value = nowHHMM();
            });
        });

        // --- AJAX submit for per-row attendance-mark forms ---
        document.querySelectorAll('form.attendance-mark').forEach(form => {
            form.addEventListener('submit', async function(e){
                e.preventDefault();
                // normalize inputs
                const ci = form.querySelector('input[name="check_in"]');
                const co = form.querySelector('input[name="check_out"]');
                const statusSel = form.querySelector('select[name="status"]');
                if (ci && ci.value) ci.value = normalizeToHHMM(ci.value);
                if (co && co.value) {
                    co.value = normalizeToHHMM(co.value);
                    // if check_out provided but status absent -> set to present
                    if (statusSel && statusSel.value === 'absent') statusSel.value = 'present';
                }

                const fd = new FormData(form);
                // ensure CSRF
                const token = document.querySelector('meta[name="csrf-token"]');
                if (token && !fd.has('_token')) fd.append('_token', token.getAttribute('content'));

                // disable submit button to prevent double submit
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                try {
                    const resp = await fetch(form.action, { method: (form.getAttribute('method') || 'POST').toUpperCase(), body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });

                    if (!resp.ok) {
                        if (resp.status === 422) {
                            try {
                                const json = await resp.json();
                                const errors = json.errors || json;
                                const messages = [];
                                if (errors && typeof errors === 'object') {
                                    for (const k in errors) {
                                        if (!errors.hasOwnProperty(k)) continue;
                                        const v = errors[k];
                                        if (Array.isArray(v)) messages.push(v.join(', '));
                                        else if (typeof v === 'string') messages.push(v);
                                        else messages.push(JSON.stringify(v));
                                    }
                                } else if (typeof errors === 'string') messages.push(errors);
                                alert('Validation error:\n' + messages.join('\n'));
                                return;
                            } catch (err) {
                                alert('Validation failed (422)');
                                return;
                            }
                        }
                        throw new Error('Request failed: ' + resp.status);
                    }

                    const data = await resp.json();

                    // update the form inputs to reflect normalized/saved values (server may return updated attendance)
                    if (data.attendance) {
                        const a = data.attendance;
                        if (ci) ci.value = a.check_in ? a.check_in : ci.value;
                        if (co) co.value = a.check_out ? a.check_out : co.value;
                        if (statusSel && a.status) statusSel.value = a.status;
                        // Update recent attendance table row if present (match by attendance id)
                        if (a.id) {
                            const rows = Array.from(document.querySelectorAll('table tbody tr'));
                            for (const r of rows) {
                                const firstTd = r.querySelector('td');
                                if (!firstTd) continue;
                                if (String(firstTd.textContent).trim() === String(a.id)) {
                                    const tds = r.querySelectorAll('td');
                                    // columns: #, Employee, Date, Check In, Check Out, Status, Actions
                                    if (tds[3]) tds[3].textContent = a.check_in ?? '-';
                                    if (tds[4]) tds[4].textContent = a.check_out ?? '-';
                                    if (tds[5]) tds[5].textContent = a.status ?? tds[5].textContent;
                                    break;
                                }
                            }
                        }
                    }

                    // show quick feedback
                    const savedLabel = document.createElement('span');
                    savedLabel.textContent = 'Saved';
                    savedLabel.className = 'ml-2 text-sm text-green-600';
                    // remove existing saved label if any
                    const existing = form.querySelector('.saved-indicator');
                    if (existing) existing.remove();
                    savedLabel.classList.add('saved-indicator');
                    form.appendChild(savedLabel);
                    setTimeout(() => savedLabel.remove(), 2500);

                } catch (err) {
                    alert('Error: ' + (err.message || err));
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        });

        // --- bulk-action handler (unchanged, uses normalized values) ---
        document.querySelectorAll('.bulk-action').forEach(btn => {
            btn.addEventListener('click', async function(){
                const status = this.dataset.status;
                const selected = checkboxes.filter(cb => cb.checked).map(cb => cb.value);
                if (!selected.length) { alert('Select at least one employee'); return; }
                const form = document.getElementById('bulkForm');
                const fd = new FormData();
                const token = form.querySelector('input[name=_token]');
                if (token) fd.append('_token', token.value);
                fd.append('date', form.querySelector('input[name=date]').value);

                const rawCheckIn = form.querySelector('input[name=check_in]').value;
                const rawCheckOut = form.querySelector('input[name=check_out]').value;
                const checkIn = normalizeToHHMM(rawCheckIn);
                const checkOut = normalizeToHHMM(rawCheckOut);

                if (status === 'checkout') {
                    fd.append('status', 'present');
                    if (!checkOut) {
                        if (!confirm('No check-out time set. Proceed and clear check_out for selected employees?')) return;
                    } else {
                        fd.append('check_out', checkOut);
                    }
                } else {
                    fd.append('status', status);
                    if (checkIn) fd.append('check_in', checkIn);
                    if (checkOut) fd.append('check_out', checkOut);
                }

                selected.forEach(id => fd.append('employee_ids[]', id));

                try {
                    const res = await fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });

                    if (!res.ok) {
                        if (res.status === 422) {
                            try {
                                const json = await res.json();
                                const errors = json.errors || json;
                                const messages = [];
                                if (errors && typeof errors === 'object') {
                                    for (const key in errors) {
                                        if (!errors.hasOwnProperty(key)) continue;
                                        const v = errors[key];
                                        if (Array.isArray(v)) messages.push(v.join(', '));
                                        else if (typeof v === 'string') messages.push(v);
                                        else messages.push(JSON.stringify(v));
                                    }
                                } else if (typeof errors === 'string') {
                                    messages.push(errors);
                                }
                                alert('Validation error:\n' + (messages.length ? messages.join('\n') : ('Status ' + res.status)));
                                return;
                            } catch (e) {
                                alert('Validation failed (status 422)');
                                return;
                            }
                        }
                        throw new Error('Request failed: ' + res.status);
                    }

                    const json = await res.json();
                    alert('Marked ' + (json.marked ? json.marked.length : selected.length) + ' employees');
                    location.reload();
                } catch (err) {
                    alert('Error: ' + (err.message || err));
                }
            });
        });
    });
    </script>
</div>
