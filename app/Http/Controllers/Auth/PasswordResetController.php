<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Mail\SendPasswordResetOtp;
use App\Models\User;

class PasswordResetController extends Controller
{
    /**
     * Send OTP to user's email for password reset.
     */
    public function forgot(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $otp = rand(100000, 999999);
        $cacheKey = 'password_reset_otp_' . $request->email;
        Cache::put($cacheKey, (string)$otp, now()->addMinutes(10));

        Mail::to($request->email)->send(new SendPasswordResetOtp($otp));

        // Audit log for OTP sent
        $user = User::where('email', $request->email)->first();
        log_audit(
            'forgot_password',
            $user,
            null,
            ['email' => $request->email, 'otp' => $otp],
            'Password reset OTP sent to: ' . $request->email
        );

        return response()->json([
            'message' => 'An OTP has been sent to your email.'
        ], Response::HTTP_OK);
    }

    /**
     * Validate OTP and reset the user's password.
     */
    public function reset(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email|exists:users,email',
            'otp'      => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $cacheKey = 'password_reset_otp_' . $data['email'];
        $cachedOtp = Cache::get($cacheKey);

        if (! $cachedOtp || $cachedOtp !== $data['otp']) {
            return response()->json([
                'error' => 'Invalid or expired OTP.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Update password
        $user = User::where('email', $data['email'])->first();
        $user->password = Hash::make($data['password']);
        $user->save();

        // Clear OTP cache
        Cache::forget($cacheKey);

        // Audit log for password reset
        log_audit(
            'reset_password',
            $user,
            null,
            null,
            'Password reset using OTP for: ' . $user->email
        );

        return response()->json([
            'message' => 'Password has been reset successfully.'
        ], Response::HTTP_OK);
    }
}
