<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;

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
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $otp = rand(100000, 999999);
        session([
            'register_data' => [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ],
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(5)
        ]);

        // Gửi mail
        Mail::to($request->email)->send(new \App\Mail\VerifyOtpMail($otp));

        return redirect()->route('verify.otp');
    }
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        $sessionOtp = session('otp');
        $expires = session('otp_expires_at');
        $data = session('register_data');

        if (!$data) {
            return redirect('/register')->with('error', 'Hết phiên đăng ký!');
        }

        if ($request->otp != $sessionOtp) {
            return back()->with('error', 'OTP không đúng');
        }

        if (now()->gt($expires)) {
            return back()->with('error', 'OTP đã hết hạn');
        }
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);
        session()->forget(['register_data', 'otp', 'otp_expires_at']);

        Auth::login($user);

        return redirect('/')->with('success', 'Xác thực thành công!');
    }
    public function showVerifyOtpForm()
    {
        if (!session()->has('verify_email')) {
            return redirect()->route('register');
        }
        return view('auth.verify-otp');
    }
    public function resendOtp()
    {
        if (!session()->has('register_data')) {
            return redirect('/register')->with('error', 'Vui lòng đăng ký lại!');
        }

        // chống spam (60s)
        if (session('otp_sent_time') && now()->diffInSeconds(session('otp_sent_time')) < 60) {
            return back()->with('error', 'Vui lòng đợi 60 giây để gửi lại OTP!');
        }

        $otp = rand(100000, 999999);

        // cập nhật lại session
        session([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(5),
            'otp_sent_time' => now()
        ]);

        $email = session('register_data.email');

        Mail::to($email)->send(new \App\Mail\VerifyOtpMail($otp));

        return back()->with('success', 'Đã gửi lại OTP!');
    }
}
