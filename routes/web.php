<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



// Backwards-compatible 'home' route used by some layouts/components.
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// (debug route removed) Recommendations are rendered on the main rewards index page.

require __DIR__.'/auth.php';

// HR routes (protected by auth + role: admin or hr)
// Allow both 'admin' and 'hr' roles to access HR area (was hr_manager previously)
Route::prefix('hr')->name('hr.')->middleware(['auth', \Spatie\Permission\Middleware\RoleMiddleware::using('admin|hr')])->group(function () {
    Route::resource('employees', App\Http\Controllers\HR\EmployeeController::class)->except(['show']);
    // Employee profile (separate page inside HR area)
    Route::get('employees/{employee}/profile', [App\Http\Controllers\HR\EmployeeController::class, 'profile'])->name('employees.profile');
    Route::patch('employees/{employee}/contact', [App\Http\Controllers\HR\EmployeeController::class, 'updateContact'])->name('employees.contact.update');
    Route::post('employees/{employee}/archive', [App\Http\Controllers\HR\EmployeeController::class, 'archive'])->name('employees.archive');
    Route::post('employees/{id}/restore', [App\Http\Controllers\HR\EmployeeController::class, 'restore'])->name('employees.restore');

    Route::get('dashboard', [App\Http\Controllers\HR\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('attendances', App\Http\Controllers\HR\AttendanceController::class);
    Route::post('attendances/bulk-mark', [App\Http\Controllers\HR\AttendanceController::class, 'bulkMark'])->name('attendances.bulkMark');
    Route::post('attendances/mark', [App\Http\Controllers\HR\AttendanceController::class, 'mark'])->name('attendances.mark');
    Route::get('performances/export-pdf', [App\Http\Controllers\HR\PerformanceController::class, 'exportPdf'])->name('performances.exportPdf');
    Route::resource('performances', App\Http\Controllers\HR\PerformanceController::class);
    // Settings
    Route::get('settings', [App\Http\Controllers\HR\SettingsController::class, 'index'])->name('settings.index');
    // Profile & password endpoints used by the inline HR Settings panel (AJAX)
    Route::post('settings/profile', [App\Http\Controllers\HR\SettingsController::class, 'profile'])->name('settings.profile');
    Route::post('settings/password', [App\Http\Controllers\HR\SettingsController::class, 'password'])->name('settings.password');
    Route::post('settings', [App\Http\Controllers\HR\SettingsController::class, 'update'])->name('settings.update');
    Route::resource('rewards', App\Http\Controllers\HR\RewardPenaltyController::class);

    // Payroll: list, show details, mark paid, and print salary receipt
    Route::get('payroll', [App\Http\Controllers\HR\PayrollController::class, 'index'])->name('payroll.index');
    Route::get('payroll/{employee}', [App\Http\Controllers\HR\PayrollController::class, 'show'])->name('payroll.show');
    Route::post('payroll/{employee}/pay', [App\Http\Controllers\HR\PayrollController::class, 'pay'])->name('payroll.pay');
    Route::get('payroll/print/{salary}', [App\Http\Controllers\HR\PayrollController::class, 'print'])->name('payroll.print');
    // Recommendations & analytics for rewards/penalties
    Route::get('rewards/analytics', [App\Http\Controllers\HR\RewardPenaltyController::class, 'analytics'])->name('rewards.analytics');
    Route::post('rewards/{reward}/approve', [App\Http\Controllers\HR\RewardPenaltyController::class, 'approve'])->name('rewards.approve');
    // Departments management (CRUD)
    Route::get('departments/export-pdf', [App\Http\Controllers\HR\DepartmentController::class, 'exportPdf'])->name('departments.exportPdf');
    Route::resource('departments', App\Http\Controllers\HR\DepartmentController::class)->except(['show']);
});

Route::get('attendances/export-pdf', [App\Http\Controllers\HR\AttendanceController::class, 'exportPdf'])->middleware('auth')->name('hr.attendances.exportPdf');
Route::get('rewards/export-pdf', [App\Http\Controllers\HR\RewardPenaltyController::class, 'exportPdf'])->middleware('auth')->name('hr.rewards.exportPdf');
Route::get('payroll/export', [App\Http\Controllers\HR\PayrollController::class, 'export'])->middleware('auth')->name('hr.payroll.export');

// Public Employees module (separate folder next to HR) — reuses HR controller logic
Route::prefix('employees')->name('employees.')->middleware(['auth'])->group(function () {
    Route::get('{employee}/profile', [App\Http\Controllers\Employees\EmployeeController::class, 'profile'])->name('profile');
    Route::patch('{employee}/contact', [App\Http\Controllers\Employees\EmployeeController::class, 'updateContact'])->name('contact.update');
    // allow employee to update their full profile (self) via PATCH
    Route::patch('{employee}/update', [App\Http\Controllers\Employees\EmployeeController::class, 'selfUpdate'])->name('self.update');
    Route::post('{employee}/leave', [App\Http\Controllers\Employees\EmployeeController::class, 'storeLeave'])->name('leave.submit');
});

// Temporary dev helper: assign 'admin' role to the current user when APP_DEBUG=true
if (config('app.debug')) {
    Route::get('dev/make-admin', function () {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login');
        }
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('admin');
        }
        return redirect()->route('hr.dashboard');
    })->middleware('auth')->name('dev.make_admin');
}

// Temporary debug route to test rewards PDF export WITHOUT auth/role middleware.
// This is intentionally only enabled when APP_DEBUG=true so it can be used for local diagnostics.
if (config('app.debug')) {
    Route::get('debug/rewards/export-pdf-test', function () {
        // Forward the current request to the controller's exportPdf method using an instantiated controller.
        // Calling the instance method avoids the "Non-static method ... cannot be called statically" error.
        $controller = app()->make(\App\Http\Controllers\HR\RewardPenaltyController::class);
        return $controller->exportPdf(request());
    })->name('debug.rewards.exportPdfTest');
}

// Debug route for attendance PDF export (local only, enabled when APP_DEBUG=true)
if (config('app.debug')) {
    Route::get('debug/attendances/export-pdf-test', function () {
        $controller = app()->make(\App\Http\Controllers\HR\AttendanceController::class);
        return $controller->exportPdf(request());
    })->name('debug.attendances.exportPdfTest');
}
