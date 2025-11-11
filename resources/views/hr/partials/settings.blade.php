@extends('layouts.hr')
@section('hr-content')
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold mb-2">System Settings</h2>
        <p class="text-sm text-gray-500 mb-4">Configure general settings affecting attendance, performance and PDF report headers.</p>

        <form id="settingsForm" method="POST" action="{{ route('hr.settings.update') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Company Name</label>
                    <input name="company_name" value="{{ $keys['company_name'] ?? config('app.name') }}"
                        class="mt-1 p-2 border rounded w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Workday Start</label>
                    <input name="workday_start" value="{{ $keys['workday_start'] ?? '08:00' }}"
                        class="mt-1 p-2 border rounded w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Workday End</label>
                    <input name="workday_end" value="{{ $keys['workday_end'] ?? '17:00' }}"
                        class="mt-1 p-2 border rounded w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Attendance Threshold (%)</label>
                    <input name="attendance_threshold" type="number" step="0.1"
                        value="{{ $keys['attendance_threshold'] ?? 75 }}" class="mt-1 p-2 border rounded w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Performance Weight (%)</label>
                    <input name="performance_weight" type="number" step="0.1"
                        value="{{ $keys['performance_weight'] ?? 70 }}" class="mt-1 p-2 border rounded w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Attendance Weight (%)</label>
                    <input name="attendance_weight" type="number" step="0.1"
                        value="{{ $keys['attendance_weight'] ?? 30 }}" class="mt-1 p-2 border rounded w-full" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">PDF Report Header (simple HTML allowed)</label>
                <textarea name="pdf_header" class="mt-1 p-2 border rounded w-full" rows="4">{{ $keys['pdf_header'] ?? '<h1>Company Report</h1>' }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <button id="saveSettingsBtn" type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
                <button id="resetDefaults" type="button" class="px-4 py-2 bg-gray-100 rounded">Restore Defaults</button>
                <span id="settingsStatus" class="text-sm text-green-600 hidden">Saved</span>
            </div>
        </form>
    </div>

    <script>
        (function() {
            const form = document.getElementById('settingsForm');
            const status = document.getElementById('settingsStatus');
            const saveBtn = document.getElementById('saveSettingsBtn');
            const resetBtn = document.getElementById('resetDefaults');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                saveBtn.disabled = true;
                status.classList.add('hidden');
                const data = new FormData(form);
                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: data,
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error('Status ' + res.status);
                    const json = await res.json();
                    status.textContent = json.message || 'Saved';
                    status.classList.remove('hidden');
                } catch (err) {
                    alert('An error occurred while saving settings: ' + (err.message || err));
                } finally {
                    saveBtn.disabled = false;
                }
            });

            resetBtn.addEventListener('click', function() {
                if (!confirm('Restore default settings?')) return;
                form.company_name.value = '{{ config('app.name') }}';
                form.workday_start.value = '08:00';
                form.workday_end.value = '17:00';
                form.attendance_threshold.value = 75;
                form.performance_weight.value = 70;
                form.attendance_weight.value = 30;
                form.pdf_header.value = '<h1>Company Report</h1>';
            });
        })();
    </script>

    <!-- Profile card -->
    <div class="bg-white p-6 rounded-xl shadow-md mt-6">
        <h2 class="text-2xl font-bold mb-2">Profile (HR)</h2>
        <p class="text-sm text-gray-500 mb-4">Update your name, email and change password from here.</p>

        <form id="profileForm" class="space-y-4" method="POST" action="{{ route('hr.settings.profile') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input name="name" id="pf_name" value="{{ auth()->user()->name ?? '' }}"
                        class="mt-1 p-2 border rounded w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input name="email" id="pf_email" value="{{ auth()->user()->email ?? '' }}"
                        class="mt-1 p-2 border rounded w-full" />
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button id="saveProfileBtn" type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Save Profile</button>
                <span id="profileStatus" class="text-sm text-green-600 hidden">Profile Saved</span>
            </div>
        </form>

        <hr class="my-4" />

        <form id="passwordForm" class="space-y-4" method="POST" action="{{ route('hr.settings.password') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Password</label>
                    <input name="current_password" type="password" class="mt-1 p-2 border rounded w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">New Password</label>
                    <input name="password" type="password" class="mt-1 p-2 border rounded w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input name="password_confirmation" type="password" class="mt-1 p-2 border rounded w-full" />
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button id="savePasswordBtn" type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Change Password</button>
                <span id="passwordStatus" class="text-sm text-green-600 hidden">Password Changed</span>
            </div>
        </form>
    </div>

    <script>
        (function() {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

            // Profile form
            const profileForm = document.getElementById('profileForm');
            const profileStatus = document.getElementById('profileStatus');
            const saveProfileBtn = document.getElementById('saveProfileBtn');
            if (profileForm) {
                profileForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    saveProfileBtn.disabled = true;
                    profileStatus.classList.add('hidden');
                    const data = new FormData(profileForm);
                    try {
                        const res = await fetch(profileForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: data,
                            credentials: 'same-origin'
                        });
                        const json = await res.json();
                        if (!res.ok) throw new Error(json.message || res.statusText);
                        profileStatus.textContent = json.message || 'Saved';
                        profileStatus.classList.remove('hidden');
                    } catch (err) {
                        alert('Error updating profile: ' + (err.message || err));
                    } finally {
                        saveProfileBtn.disabled = false;
                    }
                });
            }

            // Password form
            const passwordForm = document.getElementById('passwordForm');
            const passwordStatus = document.getElementById('passwordStatus');
            const savePasswordBtn = document.getElementById('savePasswordBtn');
            if (passwordForm) {
                passwordForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    savePasswordBtn.disabled = true;
                    passwordStatus.classList.add('hidden');
                    const data = new FormData(passwordForm);
                    try {
                        const res = await fetch(passwordForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: data,
                            credentials: 'same-origin'
                        });
                        const json = await res.json();
                        if (!res.ok) throw new Error(json.message || res.statusText);
                        passwordStatus.textContent = json.message || 'Changed';
                        passwordStatus.classList.remove('hidden');
                        passwordForm.reset();
                    } catch (err) {
                        try {
                            // attempt to parse validation response
                            const txt = await err.response?.text?.();
                            alert(txt || err.message || 'Error changing password');
                        } catch (e) {
                            alert('Error changing password: ' + (err.message || err));
                        }
                    } finally {
                        savePasswordBtn.disabled = false;
                    }
                });
            }
        })();
    </script>
@endsection
