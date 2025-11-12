@if(request()->ajax() || request()->query('inline'))
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">New Attendance Record</h2>
        <form id="ajaxForm" method="POST" action="{{ route('hr.attendances.store') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Employee</label>
                    <select name="employee_id" class="w-full p-2 border rounded">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Date</label>
                    <input type="date" name="date" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>Check In</label>
                    <div class="flex items-center gap-1">
                        <input type="time" name="check_in" step="1" placeholder="HH:MM" class="w-full p-2 border rounded time-input">
                        <button type="button" class="now-btn ml-2 px-3 py-1 bg-gray-200 rounded text-sm" title="Set current time">Now</button>
                    </div>
                </div>
                <div>
                    <label>Check Out</label>
                    <div class="flex items-center gap-1">
                        <input type="time" name="check_out" step="1" placeholder="HH:MM" class="w-full p-2 border rounded time-input">
                        <button type="button" class="now-btn ml-2 px-3 py-1 bg-gray-200 rounded text-sm" title="Set current time">Now</button>
                    </div>
                </div>
                <div>
                    <label>Status</label>
                    <select name="status" class="w-full p-2 border rounded">
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="remote">Remote</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>

    <script>
    (function(){
        // helpers
        function nowHHMM(){ const d=new Date(); return String(d.getHours()).padStart(2,'0')+':'+String(d.getMinutes()).padStart(2,'0'); }
        function normalizeToHHMM(v){ if(!v) return ''; const p=v.split(':'); return p.length>=2 ? p[0].padStart(2,'0')+':'+p[1].padStart(2,'0') : v; }

        // set Now buttons
        document.querySelectorAll('.now-btn').forEach(b=>b.addEventListener('click', function(){
            const container = this.closest('div') || this.closest('form') || document;
            const input = container.querySelector('.time-input');
            if (input) input.value = nowHHMM();
        }));

        // normalize and enforce checkout logic before ajax submit
        const ajaxForm = document.getElementById('ajaxForm');
        if (ajaxForm) {
            ajaxForm.addEventListener('submit', function(e){
                // normalize times
                const ci = ajaxForm.querySelector('input[name="check_in"]');
                const co = ajaxForm.querySelector('input[name="check_out"]');
                const status = ajaxForm.querySelector('select[name="status"]');
                if (ci && ci.value) ci.value = normalizeToHHMM(ci.value);
                if (co && co.value) {
                    const coVal = normalizeToHHMM(co.value);
                    co.value = coVal;
                    // if check_out provided but status absent -> set to present
                    if (status && status.value === 'absent') status.value = 'present';
                }
                // let normal AJAX handler proceed (if exists)
            });
        }
    })();
    </script>

@else
    @extends('layouts.app')

    @section('content')
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">New Attendance Record</h2>
            <form id="mainForm" method="POST" action="{{ route('hr.attendances.store') }}">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label>Employee</label>
                        <select name="employee_id" class="w-full p-2 border rounded">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Date</label>
                        <input type="date" name="date" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>Check In</label>
                        <div class="flex items-center gap-1">
                            <input type="time" name="check_in" step="1" placeholder="HH:MM" class="w-full p-2 border rounded time-input">
                            <button type="button" class="now-btn ml-2 px-3 py-1 bg-gray-200 rounded text-sm" title="Set current time">Now</button>
                        </div>
                    </div>
                    <div>
                        <label>Check Out</label>
                        <div class="flex items-center gap-1">
                            <input type="time" name="check_out" step="1" placeholder="HH:MM" class="w-full p-2 border rounded time-input">
                            <button type="button" class="now-btn ml-2 px-3 py-1 bg-gray-200 rounded text-sm" title="Set current time">Now</button>
                        </div>
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status" class="w-full p-2 border rounded">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="remote">Remote</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    @endsection

    <script>
    (function(){
        function nowHHMM(){ const d=new Date(); return String(d.getHours()).padStart(2,'0')+':'+String(d.getMinutes()).padStart(2,'0'); }
        function normalizeToHHMM(v){ if(!v) return ''; const p=v.split(':'); return p.length>=2 ? p[0].padStart(2,'0')+':'+p[1].padStart(2,'0') : v; }

        document.querySelectorAll('.now-btn').forEach(b=>b.addEventListener('click', function(){
            const container = this.closest('div') || this.closest('form') || document;
            const input = container.querySelector('.time-input');
            if (input) input.value = nowHHMM();
        }));

        const mainForm = document.getElementById('mainForm');
        if (mainForm) {
            mainForm.addEventListener('submit', function(e){
                const ci = mainForm.querySelector('input[name="check_in"]');
                const co = mainForm.querySelector('input[name="check_out"]');
                const status = mainForm.querySelector('select[name="status"]');
                if (ci && ci.value) ci.value = normalizeToHHMM(ci.value);
                if (co && co.value) {
                    co.value = normalizeToHHMM(co.value);
                    if (status && status.value === 'absent') status.value = 'present';
                }
                // allow normal POST submit
            });
        }
    })();
    </script>
@endif
