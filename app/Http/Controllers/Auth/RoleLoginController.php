<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;


use App\Models\User;

class RoleLoginController extends Controller
{
    public function showLoginForm($role)
    {
        $validRoles = ['rico', 'billing', 'audit', 'admin'];
        if (!in_array($role, $validRoles)) {
            abort(404);
        }
        return view('auth.role-login', compact('role'));
    }

    public function login(Request $request, $role)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = DB::table('users')
            ->where('email', $request->email)
            ->where('role', $role)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Session::put('role_user', $user);
            Session::put('role', $role);

            return redirect()->route("{$role}.dashboard");
        }

        return back()->withErrors(['login' => 'Invalid credentials or role.']);
    }

    public function logout()
    {
        Session::forget(['role_user', 'role']);
        return redirect()->route('home')->with('success', 'Logged out successfully');
    }
}
