@extends('layouts.hr')

@section('hr-content')
    @include('hr.partials.performances_table')
@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('chartPerformance');
        if (ctx) {
            const chartLabels = {!! json_encode(array_keys($chartData ?? [])) !!};
            const chartValues = {!! json_encode(array_values($chartData ?? [])) !!};
            // create gradient fill
            const g = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            g.addColorStop(0, 'rgba(99,102,241,0.18)');
            g.addColorStop(1, 'rgba(99,102,241,0.02)');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels.length ? chartLabels : ['Week 1','Week 2','Week 3','Week 4'],
                    datasets: [{
                        label: 'Performance %',
                        data: chartValues.length ? chartValues : [72,78,85,80],
                        borderColor: '#6366F1',
                        backgroundColor: g,
                        fill: true,
                        tension: 0.28,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, max: 100 } }
                }
            });
        }

        // 30-day performance chart with short date labels and gradient
        const ctx30 = document.getElementById('chart30Days');
        if (ctx30) {
            const chart30Labels = {!! json_encode($labels30 ?? []) !!};
            const chart30Values = {!! json_encode($values30 ?? []) !!};
            const chart30Rolling = {!! json_encode($rolling7 ?? []) !!};
            // format labels to short form (e.g., "Nov 3")
            const displayLabels = chart30Labels.map(d => {
                try { return new Date(d).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }); } catch (e) { return d; }
            });
            const g2 = ctx30.getContext('2d').createLinearGradient(0, 0, 0, 240);
            g2.addColorStop(0, 'rgba(16,185,129,0.12)');
            g2.addColorStop(1, 'rgba(16,185,129,0.02)');
            new Chart(ctx30, {
                type: 'line',
                data: {
                    labels: displayLabels.length ? displayLabels : [],
                    datasets: [
                        {
                            label: 'Avg Score',
                            data: chart30Values.length ? chart30Values : [],
                            borderColor: '#10B981',
                            backgroundColor: g2,
                            fill: true,
                            tension: 0.22,
                            pointRadius: 2
                        },
                        {
                            label: '7-day MA',
                            data: chart30Rolling.length ? chart30Rolling : [],
                            borderColor: '#F59E0B',
                            backgroundColor: 'rgba(245,158,11,0.06)',
                            fill: false,
                            tension: 0.2,
                            borderDash: [6,4],
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'top' } },
                    scales: { y: { beginAtZero: true, max: 100 }, x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 10 } } }
                }
            });
        }

        // Rewards / Penalties 12-month chart
        const ctxR = document.getElementById('chartRewards');
        if (ctxR) {
            const labelsR = {!! json_encode($labelsRewards ?? []) !!};
            const rewardsVals = {!! json_encode($rewardsPerMonth ?? []) !!};
            const penaltiesVals = {!! json_encode($penaltiesPerMonth ?? []) !!};
            const gR = ctxR.getContext('2d').createLinearGradient(0, 0, 0, 180);
            gR.addColorStop(0, 'rgba(99,102,241,0.12)');
            gR.addColorStop(1, 'rgba(99,102,241,0.02)');
            new Chart(ctxR, {
                type: 'bar',
                data: {
                    labels: labelsR,
                    datasets: [
                        { label: 'Rewards', data: rewardsVals, backgroundColor: 'rgba(16,185,129,0.9)' },
                        { label: 'Penalties', data: penaltiesVals, backgroundColor: 'rgba(239,68,68,0.9)' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // Composite performance chart (top composites)
        const ctxC = document.getElementById('chartComposite');
        if (ctxC) {
            const compData = {!! json_encode(array_column($topComposites ?? [], 'composite')) !!};
            const compLabels = {!! json_encode(array_column($topComposites ?? [], 'name')) !!};
            new Chart(ctxC, {
                type: 'bar',
                data: {
                    labels: compLabels,
                    datasets: [{ label: 'Composite Score', data: compData, backgroundColor: 'rgba(59,130,246,0.9)' }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: false } } }
            });
        }

        // Sidebar uses real routes now. In-page tab activation removed so each module is a separate page.

        // --- AJAX modal for quick actions ---
        function createModal() {
            const modal = document.createElement('div');
            modal.id = 'ajaxModal';
            modal.className = 'fixed inset-0 z-50 hidden items-center justify-center';
            modal.innerHTML = `
                <div class="absolute inset-0 bg-black opacity-40"></div>
                <div class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 id="ajaxModalTitle" class="text-lg font-semibold">Form</h3>
                        <button id="ajaxModalClose" class="text-gray-500">✕</button>
                    </div>
                    <div id="ajaxModalBody" class="p-4"></div>
                </div>
            `;
            document.body.appendChild(modal);
            return modal;
        }

        const modal = createModal();
        const modalBody = modal.querySelector('#ajaxModalBody');
        const modalClose = modal.querySelector('#ajaxModalClose');

        function showModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        function hideModal() { modal.classList.remove('flex'); modal.classList.add('hidden'); modalBody.innerHTML = ''; }
        modalClose.addEventListener('click', hideModal);

        // CSRF token
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // simple toast helper
        function showToast(message, type = 'info') {
            const colors = { info: 'bg-blue-500', success: 'bg-green-500', warn: 'bg-yellow-500', error: 'bg-red-500' };
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 left-6 z-60 text-white px-4 py-2 rounded shadow ${colors[type] || colors.info}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(8px)'; }, 2500);
            setTimeout(() => toast.remove(), 3000);
        }

        // Common fetch + show form logic used by clicks or form.submits
        async function fetchAndShowForm(url, tab, inline) {
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' });
                    if (!res.ok) throw new Error('Failed to load form (status ' + res.status + ')');
                    const html = await res.text();
                    // If the server accidentally returned a full HTML page (layout + nav),
                    // try to extract the first <form> from the response so the modal shows only the form.
                    let injected = html;
                    try {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const form = doc.querySelector('form');
                        if (form) {
                            injected = '';
                            const heading = form.previousElementSibling && (form.previousElementSibling.tagName === 'H2' || form.previousElementSibling.tagName === 'H3')
                                ? '<div class="mb-2">' + form.previousElementSibling.outerHTML + '</div>'
                                : '';
                            injected = heading + form.outerHTML;
                        }
                    } catch (err) {
                        injected = html;
                    }
                    modalBody.innerHTML = injected;
                    showModal();

                    // attach submit handler for the injected form
                    const injectedForm = modalBody.querySelector('form');
                    if (injectedForm) {
                        injectedForm.addEventListener('submit', async (ev) => {
                            ev.preventDefault();
                            const fm = new FormData(injectedForm);
                            // ensure CSRF token present
                            if (!fm.has('_token')) fm.append('_token', csrf);
                            const action = injectedForm.getAttribute('action') || url;
                            const method = (injectedForm.getAttribute('method') || 'POST').toUpperCase();
                            const fetchOpts = { method: method, body: fm, headers: { 'X-Requested-With': 'XMLHttpRequest' } };
                            const resp = await fetch(action, Object.assign(fetchOpts, { credentials: 'same-origin' }));
                            if (resp.status === 419) {
                                modalBody.innerHTML = '<div class="p-4 text-red-600">Session expired. Please reload the page and sign in again.</div>';
                                return;
                            }
                            if (resp.ok) {
                                // close and refresh the tab contents if inline, else reload
                                hideModal();
                                if (inline && tab) {
                                    await refreshTab(tab);
                                } else {
                                    location.reload();
                                }
                            } else {
                                const text = await resp.text();
                                // show server response (validation errors or full page)
                                modalBody.innerHTML = text;
                            }
                        });
                    }
            } catch (err) {
                modalBody.innerHTML = `<div class="p-4 text-red-600">${err.message}</div>`;
                showModal();
            }
        }

        // Delegate clicks for dynamic .action-tab links (works for content replaced via AJAX)
        document.addEventListener('click', async (e) => {
            const a = e.target.closest && e.target.closest('.action-tab');
            if (!a) return;
            e.preventDefault();
            const originalUrl = a.getAttribute('href');
            // force inline param to hint controllers to return form-only when possible
            const url = originalUrl && originalUrl.includes('?') ? originalUrl + '&inline=1' : (originalUrl || '') + '?inline=1';
            const tab = a.dataset.tab;
            const inline = a.dataset.inline === 'true';
            await fetchAndShowForm(url, tab, inline);
        });

        // Intercept GET forms with class inline-fetch so they act like callable forms
        document.addEventListener('submit', async (e) => {
            const form = e.target.closest && e.target.closest('form.inline-fetch');
            if (!form) return;
            e.preventDefault();
            let url = form.getAttribute('action') || window.location.href;
            // ensure inline hint present
            const hasInline = url.includes('inline=1') || new URL(url, window.location.origin).searchParams.has('inline');
            if (!hasInline) {
                url = url.includes('?') ? url + '&inline=1' : url + '?inline=1';
            }
            const tab = form.dataset.tab || (form.querySelector('button') && form.querySelector('button').dataset.tab);
            const inline = form.dataset.inline === 'true' || (form.querySelector('button') && form.querySelector('button').dataset.inline === 'true');
            await fetchAndShowForm(url, tab, inline);
        });

        // Intercept quick attendance mark forms (POST) with class attendance-mark
        document.addEventListener('submit', async (e) => {
            const form = e.target.closest && e.target.closest('form.attendance-mark');
            if (!form) return;
            e.preventDefault();
            const fm = new FormData(form);
            // ensure CSRF token present
            if (!fm.has('_token')) fm.append('_token', csrf);
            const action = form.getAttribute('action');
            try {
                const resp = await fetch(action, { method: 'POST', body: fm, headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                if (resp.status === 419) {
                    showToast('Session expired. Please reload the page and sign in again.', 'error');
                    return;
                }
                if (resp.ok) {
                    // refresh attendance tab only
                    await refreshTab('attendance');
                } else {
                    const text = await resp.text();
                    // if server returned JSON, try to parse and alert
                    try {
                        const j = await resp.json();
                        showToast(j.message || 'Operation completed', 'success');
                    } catch (err) {
                        showToast('Error: ' + (text || resp.status), 'error');
                    }
                }
            } catch (err) {
                showToast('Connection error: ' + err.message, 'error');
            }
        });

        // Intercept any inline-action forms (POST) used for archive/restore and similar quick actions
        document.addEventListener('submit', async (e) => {
            const form = e.target.closest && e.target.closest('form.inline-action');
            if (!form) return;
            e.preventDefault();
            const fm = new FormData(form);
            if (!fm.has('_token')) fm.append('_token', csrf);
            const action = form.getAttribute('action');
            try {
                const resp = await fetch(action, { method: 'POST', body: fm, headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                if (resp.status === 419) {
                    showToast('Session expired. Please reload the page and sign in again.', 'error');
                    return;
                }
                if (resp.ok) {
                    showToast('Operation successful', 'success');
                    const tab = form.dataset.tab || 'employees';
                    await refreshTab(tab);
                } else {
                    const text = await resp.text();
                    showToast('Error: ' + (text || resp.status), 'error');
                }
            } catch (err) {
                showToast('Connection error: ' + err.message, 'error');
            }
        });

        // When a switch (checkbox) changes inside attendance list, update hidden status and submit the parent form
        document.addEventListener('change', (e) => {
            const input = e.target.closest && e.target.closest('.attendance-toggle-input');
            if (!input) return;
            const form = input.closest('form');
            if (!form) return;
            const statusInput = form.querySelector('input[name="status"]');
            const checked = input.checked;
            if (statusInput) statusInput.value = checked ? 'present' : 'absent';
            // update label text (last span inside label)
            const labelText = form.querySelector('label span:last-child');
            if (labelText) labelText.textContent = checked ? 'Present' : 'Absent';
            // animate dot (optional) - toggle bg color
            const switchBg = form.querySelector('span.w-12');
            const dot = form.querySelector('.dot');
            if (switchBg) {
                if (checked) {
                    switchBg.classList.remove('bg-gray-300');
                    switchBg.classList.add('bg-green-500');
                } else {
                    switchBg.classList.remove('bg-green-500');
                    switchBg.classList.add('bg-gray-300');
                }
            }
            if (dot) {
                if (checked) dot.style.transform = 'translateX(1.25rem)'; else dot.style.transform = 'translateX(0)';
            }
            // submit the form (will be intercepted by submit listener)
            form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        });

        // Refresh a tab's contents by fetching its partial from the server
        async function refreshTab(tab) {
            try {
                const urlMap = {
                    'attendance': '{{ route('hr.attendances.index') }}',
                    'employees': '{{ route('hr.employees.index') }}',
                    'performance': '{{ route('hr.performances.index') }}',
                    'rewards': '{{ route('hr.rewards.index') }}',
                };
                const url = urlMap[tab] ? (urlMap[tab] + (urlMap[tab].includes('?') ? '&inline=1' : '?inline=1')) : null;
                if (!url) return;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' });
                if (!res.ok) throw new Error('Failed to refresh tab ' + tab + ' (status ' + res.status + ')');
                const html = await res.text();
                const container = document.getElementById('tab-' + tab);
                if (container) container.innerHTML = html;
            } catch (err) {
                console.error('refreshTab error', err);
            }
        }
    });
</script>
@endsection
