<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Models\User;

class SettingsController extends Controller
{
    public function index()
    {
        $keys = Setting::all()->pluck('value','key')->toArray();
        return view('hr.partials.settings', compact('keys'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'workday_start' => 'nullable|string',
            'workday_end' => 'nullable|string',
            'attendance_threshold' => 'nullable|numeric|min:0|max:100',
            'performance_weight' => 'nullable|numeric|min:0|max:100',
            'attendance_weight' => 'nullable|numeric|min:0|max:100',
            'pdf_header' => 'nullable|string',
        ]);

        // Save settings
        $pairs = [];
        foreach ($data as $k => $v) {
            $pairs[$k] = $v;
        }
        Setting::setMany($pairs);

        if ($request->ajax()) {
            return response()->json(['status' => 'ok','message' => 'Settings saved']);
        }
        return redirect()->back()->with('success','Settings saved');
    }

    /**
     * Update authenticated user's profile (name/email) via AJAX from HR settings.
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($data);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        return response()->json(['status' => 'ok', 'message' => 'Profile updated', 'user' => ['name' => $user->name, 'email' => $user->email]]);
    }

    /**
     * Change authenticated user's password.
     */
    public function password(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required','current_password'],
            'password' => ['required', PasswordRule::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['status' => 'ok', 'message' => 'Password updated']);
    }
}
