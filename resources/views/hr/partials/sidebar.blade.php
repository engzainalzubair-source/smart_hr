<aside class="w-64 bg-white border-r border-gray-200 shadow-sm flex flex-col">
    <div class="p-6 border-b border-gray-100 flex items-center gap-2">
        <i class="fa-solid fa-building-user text-indigo-600 text-xl"></i>
        <span class="text-xl font-extrabold text-gray-800">HR System</span>
    </div>

    <nav class="flex-1 overflow-y-auto py-4">
        @php
            // Use explicit route & pattern so we can highlight the active link
            $menu = [
                ['icon' => 'fa-chart-line', 'route' => 'hr.dashboard', 'pattern' => 'hr.dashboard', 'label' => 'Dashboard'],
                ['icon' => 'fa-users', 'route' => 'hr.employees.index', 'pattern' => 'hr.employees.*', 'label' => 'Employees'],
                ['icon' => 'fa-calendar-check', 'route' => 'hr.attendances.index', 'pattern' => 'hr.attendances.*', 'label' => 'Attendance'],
                ['icon' => 'fa-chart-pie', 'route' => 'hr.performances.index', 'pattern' => 'hr.performances.*', 'label' => 'Performance'],
                ['icon' => 'fa-gift', 'route' => 'hr.rewards.index', 'pattern' => 'hr.rewards.*', 'label' => 'Rewards'],
                ['icon' => 'fa-money-bill-transfer', 'route' => 'hr.payroll.index', 'pattern' => 'hr.payroll.*', 'label' => 'صرف الرواتب'],
                ['icon' => 'fa-building', 'route' => 'hr.departments.index', 'pattern' => 'hr.departments.*', 'label' => 'الأقسام'],
                ['icon' => 'fa-gear', 'route' => 'hr.settings.index', 'pattern' => 'hr.settings.*', 'label' => 'Settings'],
            ];
        @endphp
        <ul class="space-y-1 px-3">
            @foreach($menu as $item)
            @php $active = request()->routeIs($item['pattern'] ?? $item['route']); @endphp
            <li>
                <a href="{{ route($item['route']) }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition {{ $active ? 'bg-indigo-50 text-indigo-700 font-semibold pointer-events-none' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}"
                   aria-current="{{ $active ? 'true' : 'false' }}">
                    <i class="fa-solid {{ $item['icon'] }} w-5"></i>
                    <span class="font-medium">{{ $item['label'] }}</span>
                </a>
            </li>
            @endforeach
        </ul>
    </nav>

    <div class="p-4 border-t border-gray-100 text-sm text-gray-400 text-center">
        © 2025 HR System
    </div>
</aside>
