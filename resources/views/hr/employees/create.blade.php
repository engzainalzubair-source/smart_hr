@if(request()->ajax() || request()->query('inline'))
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">إضافة موظف جديد</h2>
        <form id="ajaxForm" method="POST" action="{{ route('hr.employees.store') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>الاسم الأول</label>
                    <input type="text" name="first_name" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label>اسم العائلة</label>
                    <input type="text" name="last_name" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label>البريد الإلكتروني</label>
                    <input type="email" name="email" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>تاريخ التوظيف</label>
                    <input type="date" name="hire_date" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>الراتب</label>
                    <input type="number" step="0.01" name="salary" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>الحالة</label>
                    <select name="status" class="w-full p-2 border rounded">
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
            </div>
        </form>
    </div>
@else
    @extends('layouts.app')

    @section('content')
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">إضافة موظف جديد</h2>
            <form method="POST" action="{{ route('hr.employees.store') }}">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label>الاسم الأول</label>
                        <input type="text" name="first_name" class="w-full p-2 border rounded" required>
                    </div>
                    <div>
                        <label>اسم العائلة</label>
                        <input type="text" name="last_name" class="w-full p-2 border rounded" required>
                    </div>
                    <div>
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>تاريخ التوظيف</label>
                        <input type="date" name="hire_date" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>الراتب</label>
                        <input type="number" step="0.01" name="salary" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>الحالة</label>
                        <select name="status" class="w-full p-2 border rounded">
                            <option value="active">Active</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
                </div>
            </form>
        </div>
    @endsection
@endif
