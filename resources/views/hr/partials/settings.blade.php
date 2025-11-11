<div class="bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold mb-2">إعدادات النظام</h2>
    <p class="text-sm text-gray-500 mb-4">ضبط إعدادات عامة لتأثير الحضور والأداء وترويسة تقارير PDF.</p>

    <form id="settingsForm" method="POST" action="{{ route('hr.settings.update') }}" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">اسم الشركة</label>
                <input name="company_name" value="{{ $keys['company_name'] ?? config('app.name') }}" class="mt-1 p-2 border rounded w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">بداية الدوام</label>
                <input name="workday_start" value="{{ $keys['workday_start'] ?? '08:00' }}" class="mt-1 p-2 border rounded w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">نهاية الدوام</label>
                <input name="workday_end" value="{{ $keys['workday_end'] ?? '17:00' }}" class="mt-1 p-2 border rounded w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">عتبة الحضور (%)</label>
                <input name="attendance_threshold" type="number" step="0.1" value="{{ $keys['attendance_threshold'] ?? 75 }}" class="mt-1 p-2 border rounded w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">وزن الأداء (%)</label>
                <input name="performance_weight" type="number" step="0.1" value="{{ $keys['performance_weight'] ?? 70 }}" class="mt-1 p-2 border rounded w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">وزن الحضور (%)</label>
                <input name="attendance_weight" type="number" step="0.1" value="{{ $keys['attendance_weight'] ?? 30 }}" class="mt-1 p-2 border rounded w-full" />
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">ترويسة تقارير PDF (HTML مسموح بسيط)</label>
            <textarea name="pdf_header" class="mt-1 p-2 border rounded w-full" rows="4">{{ $keys['pdf_header'] ?? '<h1>Company Report</h1>' }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <button id="saveSettingsBtn" type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">حفظ</button>
            <button id="resetDefaults" type="button" class="px-4 py-2 bg-gray-100 rounded">استعادة الافتراضي</button>
            <span id="settingsStatus" class="text-sm text-green-600 hidden">تم الحفظ</span>
        </div>
    </form>
</div>

<script>
(function(){
    const form = document.getElementById('settingsForm');
    const status = document.getElementById('settingsStatus');
    const saveBtn = document.getElementById('saveSettingsBtn');
    const resetBtn = document.getElementById('resetDefaults');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    form.addEventListener('submit', async function(e){
        e.preventDefault();
        saveBtn.disabled = true;
        status.classList.add('hidden');
        const data = new FormData(form);
        try {
            const res = await fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: data, credentials: 'same-origin' });
            if (!res.ok) throw new Error('Status ' + res.status);
            const json = await res.json();
            status.textContent = json.message || 'تم الحفظ';
            status.classList.remove('hidden');
        } catch (err) {
            alert('حدث خطأ أثناء حفظ الإعدادات: ' + err.message);
        } finally { saveBtn.disabled = false; }
    });

    resetBtn.addEventListener('click', function(){
        if (!confirm('استعادة الإعدادات الافتراضية؟')) return;
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
    <h2 class="text-2xl font-bold mb-2">الملف الشخصي (HR)</h2>
    <p class="text-sm text-gray-500 mb-4">قم بتحديث اسمك وبريدك الإلكتروني وتغيير كلمة المرور من هنا.</p>

    <form id="profileForm" class="space-y-4" method="POST" action="{{ route('hr.settings.profile') }}">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">الاسم</label>
                <input name="name" id="pf_name" value="{{ auth()->user()->name ?? '' }}" class="mt-1 p-2 border rounded w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                <input name="email" id="pf_email" value="{{ auth()->user()->email ?? '' }}" class="mt-1 p-2 border rounded w-full" />
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button id="saveProfileBtn" type="submit" class="px-4 py-2 bg-green-600 text-white rounded">حفظ الملف</button>
            <span id="profileStatus" class="text-sm text-green-600 hidden">تم حفظ الملف</span>
        </div>
    </form>

    <hr class="my-4" />

    <form id="passwordForm" class="space-y-4" method="POST" action="{{ route('hr.settings.password') }}">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">كلمة المرور الحالية</label>
                <input name="current_password" type="password" class="mt-1 p-2 border rounded w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">كلمة المرور الجديدة</label>
                <input name="password" type="password" class="mt-1 p-2 border rounded w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">تأكيد كلمة المرور</label>
                <input name="password_confirmation" type="password" class="mt-1 p-2 border rounded w-full" />
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button id="savePasswordBtn" type="submit" class="px-4 py-2 bg-red-600 text-white rounded">تغيير كلمة المرور</button>
            <span id="passwordStatus" class="text-sm text-green-600 hidden">تم تغيير كلمة المرور</span>
        </div>
    </form>
</div>

<script>
(function(){
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Profile form
    const profileForm = document.getElementById('profileForm');
    const profileStatus = document.getElementById('profileStatus');
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    profileForm.addEventListener('submit', async function(e){
        e.preventDefault();
        saveProfileBtn.disabled = true;
        profileStatus.classList.add('hidden');
        const data = new FormData(profileForm);
        try {
            const res = await fetch(profileForm.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: data, credentials: 'same-origin' });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || res.statusText);
            profileStatus.textContent = json.message || 'تم الحفظ';
            profileStatus.classList.remove('hidden');
            // reflect updated values in the page if necessary
        } catch (err) {
            alert('خطأ عند تحديث الملف: ' + err.message);
        } finally { saveProfileBtn.disabled = false; }
    });

    // Password form
    const passwordForm = document.getElementById('passwordForm');
    const passwordStatus = document.getElementById('passwordStatus');
    const savePasswordBtn = document.getElementById('savePasswordBtn');
    passwordForm.addEventListener('submit', async function(e){
        e.preventDefault();
        savePasswordBtn.disabled = true;
        passwordStatus.classList.add('hidden');
        const data = new FormData(passwordForm);
        try {
            const res = await fetch(passwordForm.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: data, credentials: 'same-origin' });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || res.statusText);
            passwordStatus.textContent = json.message || 'تم التغيير';
            passwordStatus.classList.remove('hidden');
            passwordForm.reset();
        } catch (err) {
            // try to surface validation errors
            try {
                const json = await err.response?.json();
                alert(json?.message || err.message);
            } catch (e) {
                alert('خطأ عند تغيير كلمة المرور: ' + err.message);
            }
        } finally { savePasswordBtn.disabled = false; }
    });
})();
</script>
