<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $role = Auth::user()->role;

            return match ($role) {
                'admin' => redirect()->route('admin.dashboard'),
                'rico' => redirect()->route('rico.dashboard'),
                'billing' => redirect()->route('billing.dashboard'),
                'audit' => redirect()->route('audit.dashboard'),
                'ccu' => redirect()->route('ccu.dashboard'),
                'rkam' => redirect()->route('rkam.dashboard'),
                'bm' => redirect()->route('bm.dashboard'),


                default => abort(403, 'Unauthorized role.'),
            };
        }

        return back()->withErrors([
            'email' => 'Invalid login credentials.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
