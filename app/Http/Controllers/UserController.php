<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Show the form for creating a new user.
     */
    // public function create()
    // {
    //     // Option 1: Hardcoded roles (simple)
    //     $roles = ['rkam','bm', 'audit', 'ccu', 'rico', 'rco', 'billing', 'manager', 'user'];

    //     // Option 2 (later): If you have a `roles` table, replace with:
    //     // $roles = Role::pluck('name');

    //     $locations = Location::all()->groupBy('region');

    //     return view('users.create', compact('roles', 'locations'));
    // }


    public function create()
{
        $roles = ['rkam','bm', 'audit', 'ccu', 'rico', 'rco', 'billing', 'manager', 'user'];

    // Group locations by region
    $locations = Location::all()->groupBy('region');

    return view('users.create', compact('roles', 'locations'));
}

    public function index()
{
    $users = \App\Models\User::with('location')->paginate(10); // eager load location
    return view('users.index', compact('users'));
}


    /**
     * Store new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|string',
            'location' => 'required|string',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role'     => $validated['role'],
            'location' => $validated['location'],
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }
}
