<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Role-based redirect:
        // - admin -> main dashboard
        // - hr -> hr dashboard
        // - otherwise: treat as employee and redirect to their profile (by matching email)
        if (method_exists($user, 'hasRole')) {
            return redirect()->intended(route('hr.dashboard', absolute: false));
        }

        // Employee: attempt to find an Employee record by the user's email.
        try {
            $employeeModel = \App\Models\Employee::where('email', $user->email)->first();
            if ($employeeModel) {
                return redirect()->intended(route('employees.profile', ['employee' => $employeeModel->id], absolute: false));
            }
        } catch (\Throwable $e) {
            // ignore and fallback to dashboard
        }

        // Fallback to default dashboard
        return redirect()->intended(route('hr.dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
