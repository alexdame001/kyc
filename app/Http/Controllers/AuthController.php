<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Hash;

class AuthController extends Controller
{
    // public function login(Request $request)
    // {
    //     $user = DB::select("EXEC login_user ?, ?", [
    //         $request->email,
    //         $request->password // Note: In production, hash & verify!
    //     ]);

    //     if (empty($user)) {
    //         return response()->json(['error' => 'Invalid credentials'], 401);
    //     }

    //     $userData = $user[0];

    public function login(Request $request)
{
    $user = DB::select("EXEC login_user ?", [$request->email]);

    if (empty($user) || !Hash::check($request->password, $user[0]->password)) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    $userData = $user[0];

        // Log login action
        DB::table('audit_logs')->insert([
            'user_id' => $userData->id,
            'action' => 'login',
            'target_table' => 'users',
            'target_id' => $userData->id,
            'details' => json_encode(['email' => $request->email]),
        ]);

        return response()->json([
        'status' => true,
        'message' => 'Login successful',
        'user' => $userData
    ]);
    }
}
