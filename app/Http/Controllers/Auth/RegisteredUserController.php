<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * Validates, sends OTP, and stores pending data in session.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'student_id'   => ['nullable', 'string', 'max:20'],
            'phone_number' => ['required', 'string', 'max:20', 'regex:/^(\+?256|0)[7][0-9]{8}$/'],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
            'verify_via'   => ['required', 'in:email,whatsapp'],
        ]);

        // Check phone uniqueness
        if (User::where('phone_number', $request->phone_number)->exists()) {
            return back()->withInput()->withErrors(['phone_number' => 'This phone number is already registered.']);
        }

        // Store pending registration in session
        session(['pending_registration' => [
            'name'                 => $request->name,
            'email'                => $request->email,
            'student_id'           => $request->student_id,
            'phone_number'         => $request->phone_number,
            'password'             => Hash::make($request->password),
            'verification_channel' => $request->verify_via,
        ]]);
        session()->save();

        // Send OTP
        $identifier = $request->verify_via === 'email' ? $request->email : $request->phone_number;
        OtpService::send($identifier, 'registration', $request->verify_via);

        return redirect()->route('register.verify.form');
    }
}
