<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Password;
    use App\Mail\StaffAccountCreated;
use Illuminate\Support\Facades\Mail;

use App\Mail\StaffWelcomeMail; // we will create this Mailable

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserProfileController extends Controller
{
    /**
     * Show staff profile & list all staff.
     */
    public function index()
    {
        $roles = ['rkam', 'bm', 'rico', 'ccu', 'audit', 'billing'];
        $staff = User::paginate(50);

        // $staff = User::all(); // fetch all staff
        return view('staff.profile', compact('roles', 'staff'));
    }



public function store(Request $request)
{
    // Validate inputs
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|string|max:20',
        'location' => 'required|string|max:100',
        'role' => 'required|string|max:50',
    ]);

    // Create user with default password
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'role' => $request->role,
        'location' => $request->location,
        'phone' => $request->phone,
        'password' => bcrypt('123456'),
        'must_change_password' => true,
    ]);

    // 🔔 Send email to staff
    Mail::to($user->email)->send(new StaffWelcomeMail($user));

    return redirect()->route('staff.index')->with('success', 'Staff created and email sent successfully.');
}


// public function store(Request $request)
// {
//     $request->validate([
//         'name' => 'required|string|max:255',
//         'email' => 'required|email|unique:users,email',
//         'role' => ['required', Rule::in(['rkam', 'bm', 'rico', 'ccu', 'audit', 'billing'])],
//         'location' => 'required|string|max:255',
//         'password' => 'required|string|confirmed|min:6',
//         'phone' => 'required|string|min:11'
//     ]);

//     $plainPassword = $request->password;

//     $staff = User::create([
//         'name' => $request->name,
//         'email' => $request->email,
//         'role' => $request->role,
//         'location' => $request->location,
//         'password' => Hash::make($plainPassword),
//         'phone' => $request->phone,
//         'must_change_password' => 1,
//     ]);

//     // 📧 Send mail
//     Mail::to($staff->email)->send(new StaffAccountCreated($staff, $plainPassword));

//     return redirect()->route('staff.profile')->with('success', 'Staff created successfully and email sent.');
// }


    /**
     * Store new staff
     */
//     public function store(Request $request)
//     {
//         $request->validate([
//             'name' => 'required|string|max:255',
//             'email' => 'required|email|unique:users,email',
//             'role' => ['required', Rule::in(['rkam', 'bm', 'rico', 'ccu', 'audit', 'billing'])],
//             'location' => 'required|string|max:255',
//             'password' => 'required|string|confirmed|min:6',
//             'phone' => 'required|string|min:11'


//         ]);

//         // User::create([
//         //     'name' => $request->name,
//         //     'email' => $request->email,
//         //     'role' => $request->role,
//         //     'location' => $request->location,
//         //     'password' => Hash::make($request->password),
//         //     'phone' => $request->phone,

//         // ]);

//         User::create([
//     'name' => $request->name,
//     'email' => $request->email,
//     'role' => $request->role,
//     'location' => $request->location,
//     'password' => Hash::make($request->password),
//     'phone' => $request->phone,
//     'must_change_password' => 1, // force first login change
// ]);


//         return redirect()->route('staff.profile')->with('success', 'Staff created successfully.');
//     }

    /**
     * Show edit form for a staff
     */
    public function edit($id)
    {
        $staff = User::findOrFail($id);
        $roles = ['rkam', 'bm', 'rico', 'ccu', 'audit', 'billing'];
        return view('staff.edit', compact('staff', 'roles'));
    }

    /**
     * Update staff details
     */
    public function update(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($staff->id)],
            'role' => ['required', Rule::in(['rkam', 'bm', 'rico', 'ccu', 'audit', 'billing'])],
            'location' => 'required|string|max:255',
        ]);

        $staff->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'location' => $request->location,
        ]);

        return redirect()->route('staff.profile')->with('success', 'Staff updated successfully.');
    }

    /**
     * Delete staff
     */
    public function destroy($id)
    {
        $staff = User::findOrFail($id);
        $staff->delete();

        return redirect()->route('staff.profile')->with('success', 'Staff deleted successfully.');
    }

    /**
     * Show change password form
     */
    public function showChangePassword($id)
    {
        $staff = User::findOrFail($id);
        return view('staff.change-password', compact('staff'));
    }

    /**
     * Update staff password
     */
    public function changePassword(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|confirmed|min:6',
        ]);

        $staff->password = Hash::make($request->password);
        $staff->save();

        return redirect()->route('staff.login.form')->with('success', 'Password updated successfully.');
    }

    /**
     * Force password reset (optional)
     */
    public function forcePasswordReset($id)
    {
        $staff = User::findOrFail($id);
        $staff->password = Hash::make('defaultpassword'); // set default
        $staff->save();

        return redirect()->route('staff.profile')->with('success', 'Password reset successfully.');
    }


    public function showChangePasswordForm()
    {
        return view('staff.change-password');
    }

public function showFirstLoginChangePasswordForm()
{
    return view('staff.first-login-change-password');
}

public function updateFirstLoginPassword(Request $request)
{
    $request->validate([
        'new_password' => 'required|string|confirmed|min:8',
    ]);

    $user = auth()->user();
    $user->password = Hash::make($request->new_password);
    $user->must_change_password = 0; // mark as changed
    $user->save();

    return redirect()->route('staff.profile')->with('success', 'Password updated successfully.');
}



//    public function updatePassword(Request $request)
// {
//     $request->validate([
//         'current_password' => 'required',
//         'new_password' => ['required','confirmed', 'min:8'],
//     ]);

//     $user = auth()->user();

//     if (!Hash::check($request->current_password, $user->password)) {
//         return back()->withErrors(['current_password' => 'Current password is incorrect']);
//     }

//     $user->password = Hash::make($request->new_password);
//     $user->must_change_password = 0; // mark as changed
//     $user->save();

//     return redirect()->route('staff.profile')->with('success', 'Password updated successfully.');
// }

// app/Http/Controllers/YourControllerName.php

public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'new_password' => ['required', 'confirmed', 'min:8'],
    ]);

    $user = auth()->user();

    if (!Hash::check($request->current_password, $user->password)) {
        return back()->withErrors(['current_password' => 'Current password is incorrect']);
    }

    // Temporarily uncomment this line to see the user object before the update.
    dd($user);

    $user->password = Hash::make($request->new_password);
    $user->must_change_password = 0; // Use 0 for consistency with database columns

    // Temporarily uncomment this line to see the user object after the update.
    // Look for the 'must_change_password' property in the output.
    // dd($user);

    // After you have debugged, remove the dd() lines and uncomment the next line.
    $user->save();

    return redirect()->route('staff.profile')->with('success', 'Password updated successfully.');
}
}
