<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffPasswordController extends Controller
{
    public function showChangeForm()
    {
        return view('staff.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->must_change_password = 0;
        $user->save();

        return redirect()->route($user->role . '.dashboard')->with('success', 'Password changed successfully!');
    }
}
