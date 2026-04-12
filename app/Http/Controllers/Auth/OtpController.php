<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class OtpController extends Controller
{
    /**
     * Show the OTP verification form after registration.
     */
    public function showVerifyForm(Request $request)
    {
        $data = session('pending_registration');
        if (!$data) {
            return redirect()->route('register');
        }

        return response()
            ->view('auth.verify-otp', [
                'identifier' => $data['verification_channel'] === 'email' ? $data['email'] : $data['phone_number'],
                'channel'    => $data['verification_channel'],
                'purpose'    => 'registration',
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Verify OTP and complete registration.
     */
    public function verifyRegistration(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $data = session('pending_registration');
        if (!$data) {
            return redirect()->route('register')->with('error', 'Registration session expired. Please try again.');
        }

        $identifier = $data['verification_channel'] === 'email' ? $data['email'] : $data['phone_number'];

        if (!OtpService::verify($identifier, 'registration', $request->code)) {
            return back()->withErrors(['code' => 'Invalid or expired verification code. Please try again.']);
        }

        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'student_id'    => $data['student_id'],
            'phone_number'  => $data['phone_number'],
            'password'      => $data['password'],
            'role'          => 'user',
            'reward_points' => 0,
            'email_verified_at' => $data['verification_channel'] === 'email' ? now() : null,
            'phone_verified_at' => $data['verification_channel'] === 'whatsapp' ? now() : null,
            'notification_preferences' => [
                'email'    => true,
                'sms'      => false,
                'whatsapp' => true,
            ],
        ]);

        session()->forget('pending_registration');

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account created successfully! Welcome to MAK Lost & Found.');
    }

    /**
     * Resend OTP code during registration.
     */
    public function resendRegistration(Request $request)
    {
        $data = session('pending_registration');
        if (!$data) {
            return redirect()->route('register');
        }

        $identifier = $data['verification_channel'] === 'email' ? $data['email'] : $data['phone_number'];
        OtpService::send($identifier, 'registration', $data['verification_channel']);

        return back()->with('status', 'A new verification code has been sent.');
    }

    /**
     * Show forgot password form (enhanced with phone option).
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password-otp');
    }

    /**
     * Send password reset OTP.
     */
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'channel'    => 'required|in:email,whatsapp',
        ]);

        $identifier = $request->identifier;
        $channel = $request->channel;

        // Find user by email or phone
        if ($channel === 'email') {
            $user = User::where('email', $identifier)->first();
        } else {
            $user = User::where('phone_number', $identifier)->first();
        }

        if (!$user) {
            return back()->withErrors(['identifier' => 'No account found with that ' . ($channel === 'email' ? 'email address' : 'phone number') . '.']);
        }

        $sendTo = $channel === 'email' ? $user->email : $user->phone_number;
        OtpService::send($sendTo, 'password_reset', $channel);

        session(['password_reset_identifier' => $sendTo, 'password_reset_channel' => $channel, 'password_reset_user_id' => $user->id]);

        return redirect()->route('password.otp.verify.form')
            ->with('status', 'Verification code sent via ' . ($channel === 'email' ? 'email' : 'WhatsApp') . '.');
    }

    /**
     * Show OTP entry form for password reset.
     */
    public function showResetOtpForm()
    {
        if (!session('password_reset_identifier')) {
            return redirect()->route('password.otp.request');
        }

        return view('auth.verify-otp', [
            'identifier' => session('password_reset_identifier'),
            'channel'    => session('password_reset_channel'),
            'purpose'    => 'password_reset',
        ]);
    }

    /**
     * Verify password reset OTP.
     */
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $identifier = session('password_reset_identifier');
        if (!$identifier) {
            return redirect()->route('password.otp.request');
        }

        if (!OtpService::verify($identifier, 'password_reset', $request->code)) {
            return back()->withErrors(['code' => 'Invalid or expired verification code.']);
        }

        session(['password_reset_verified' => true]);

        return redirect()->route('password.otp.new');
    }

    /**
     * Show new password form after OTP verification.
     */
    public function showNewPasswordForm()
    {
        if (!session('password_reset_verified')) {
            return redirect()->route('password.otp.request');
        }

        return view('auth.reset-password-otp');
    }

    /**
     * Set new password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $userId = session('password_reset_user_id');
        if (!$userId || !session('password_reset_verified')) {
            return redirect()->route('password.otp.request');
        }

        $user = User::findOrFail($userId);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        session()->forget(['password_reset_identifier', 'password_reset_channel', 'password_reset_user_id', 'password_reset_verified']);

        return redirect()->route('login')->with('status', 'Password reset successfully! Please log in with your new password.');
    }

    /**
     * Resend OTP for password reset.
     */
    public function resendResetOtp()
    {
        $identifier = session('password_reset_identifier');
        $channel = session('password_reset_channel');

        if (!$identifier || !$channel) {
            return redirect()->route('password.otp.request');
        }

        OtpService::send($identifier, 'password_reset', $channel);

        return back()->with('status', 'A new verification code has been sent.');
    }
}
