<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User; // ← Make sure this line is here

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;

class StaffLoginController extends Controller
{
    // Allowed roles
    protected $roles = ['rkam', 'bm', 'rico', 'ccu', 'audit', 'billing'];

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.staff-login');
    }

    /**
     * Handle staff login.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate login input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Attempt to authenticate
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // Force password update if required
        if ($user->must_change_password) {
            return redirect()->route('staff.change-password', $user->id);
        }

        // Redirect based on role
        switch ($user->role) {
            case 'rkam':
                return redirect()->route('rkam.dashboard');
            case 'bm':
                return redirect()->route('bm.dashboard');
            case 'rico':
                return redirect()->route('rico.dashboard');
            case 'ccu':
                return redirect()->route('ccu.dashboard');
            case 'audit':
                return redirect()->route('audit.dashboard');
            case 'billing':
                return redirect()->route('billing.dashboard');
            default:
                Auth::logout();
                return redirect()->route('staff.login.form')
                    ->withErrors(['role' => 'Unauthorized role.']);
        }
    }

    /**
     * Logout staff.
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect()->route('staff.login.form')
            ->with('success', 'Logged out successfully.');
    }

    /**
     * Show password change form.
     */
    // public function showChangePasswordForm($id)
    // {
    //     $user = Auth::user();
    //     if ($user->id != $id) {
    //         abort(403);
    //     }

    //     return view('staff.change-password', compact('user'));
    // }

    public function showChangePasswordForm($id)
{
    $staff = User::findOrFail($id);
    return view('staff.change-password', compact('staff'));
}

    /**
     * Handle password change.
     */
    public function updatePassword(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        if ($user->id != $id) {
            abort(403);
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('staff.login.form')
            ->with('success', 'Password updated successfully. Please log in.');
    }
}
