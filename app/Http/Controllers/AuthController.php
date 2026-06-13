<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Division;
use App\Models\SmsSetting;
use App\Models\Upazila;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(protected SmsService $sms)
    {
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'mobile.required' => 'মোবাইল নম্বর প্রয়োজন',
            'mobile.regex' => 'সঠিক বাংলাদেশী মোবাইল নম্বর দিন (01XXXXXXXXX)',
            'password.required' => 'পাসওয়ার্ড প্রয়োজন',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))
                ->with('success', 'স্বাগতম ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'mobile' => 'মোবাইল নম্বর বা পাসওয়ার্ড ভুল।',
        ])->onlyInput('mobile');
    }

    public function showRegister(): View
    {
        return view('auth.register', $this->geoData());
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $this->validateRegistration($request);

        // If the SMS module is on, verify the mobile via OTP before creating the account.
        if (SmsSetting::current()->status) {
            $request->session()->put('pending_registration', $validated);
            return $this->dispatchOtp($validated['mobile'], 'register', route('register.verify'),
                'নিবন্ধন সম্পন্ন করতে মোবাইলে পাঠানো OTP দিন।');
        }

        return $this->createUserAndLogin($validated, $request);
    }

    /** OTP entry screen shown after the registration form (SMS mode). */
    public function showRegisterVerify(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('pending_registration')) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp', [
            'mobile' => $request->session()->get('pending_registration')['mobile'],
            'action' => route('register.verify.submit'),
            'resendAction' => route('register.verify.resend'),
            'title' => 'নিবন্ধন যাচাই',
        ]);
    }

    public function registerVerify(Request $request): RedirectResponse
    {
        $data = $request->session()->get('pending_registration');
        if (! $data) {
            return redirect()->route('register');
        }

        $request->validate(['otp' => ['required', 'digits:6']], ['otp.required' => 'OTP কোড দিন']);

        if (! $this->sms->verifyOtp($data['mobile'], $request->otp, 'register')) {
            return back()->withErrors(['otp' => 'OTP কোড ভুল বা মেয়াদোত্তীর্ণ।']);
        }

        $request->session()->forget('pending_registration');
        return $this->createUserAndLogin($data, $request);
    }

    public function registerResend(Request $request): RedirectResponse
    {
        $data = $request->session()->get('pending_registration');
        if (! $data) {
            return redirect()->route('register');
        }

        return $this->dispatchOtp($data['mobile'], 'register', route('register.verify'), 'নতুন OTP পাঠানো হয়েছে।');
    }

    /* ---------------------------------------------------------------------
     | Login with OTP (alternative to password)
     * ------------------------------------------------------------------- */

    public function showOtpLogin(): View
    {
        return view('auth.otp-login');
    }

    public function sendLoginOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
        ], ['mobile.regex' => 'সঠিক মোবাইল নম্বর দিন']);

        if (! User::where('mobile', $request->mobile)->exists()) {
            return back()->withErrors(['mobile' => 'এই নম্বরে কোনো অ্যাকাউন্ট নেই।'])->onlyInput('mobile');
        }

        $request->session()->put('otp_login_mobile', $request->mobile);
        return $this->dispatchOtp($request->mobile, 'login', route('login.otp.verify'), 'লগইন OTP পাঠানো হয়েছে।');
    }

    public function showOtpLoginVerify(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('otp_login_mobile')) {
            return redirect()->route('login.otp');
        }

        return view('auth.verify-otp', [
            'mobile' => $request->session()->get('otp_login_mobile'),
            'action' => route('login.otp.verify.submit'),
            'resendAction' => route('login.otp.resend'),
            'title' => 'OTP দিয়ে লগইন',
        ]);
    }

    public function loginOtpResend(Request $request): RedirectResponse
    {
        $mobile = $request->session()->get('otp_login_mobile');
        if (! $mobile) {
            return redirect()->route('login.otp');
        }

        return $this->dispatchOtp($mobile, 'login', route('login.otp.verify'), 'নতুন OTP পাঠানো হয়েছে।');
    }

    public function verifyLoginOtp(Request $request): RedirectResponse
    {
        $mobile = $request->session()->get('otp_login_mobile');
        if (! $mobile) {
            return redirect()->route('login.otp');
        }

        $request->validate(['otp' => ['required', 'digits:6']], ['otp.required' => 'OTP কোড দিন']);

        if (! $this->sms->verifyOtp($mobile, $request->otp, 'login')) {
            return back()->withErrors(['otp' => 'OTP কোড ভুল বা মেয়াদোত্তীর্ণ।']);
        }

        $user = User::where('mobile', $mobile)->first();
        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->forget('otp_login_mobile');

        return redirect()->intended(route('dashboard'))->with('success', 'স্বাগতম ' . $user->name . '!');
    }

    /* ---------------------------------------------------------------------
     | Forgot / reset password via OTP
     * ------------------------------------------------------------------- */

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
        ], ['mobile.regex' => 'সঠিক মোবাইল নম্বর দিন']);

        if (! User::where('mobile', $request->mobile)->exists()) {
            return back()->withErrors(['mobile' => 'এই নম্বরে কোনো অ্যাকাউন্ট নেই।'])->onlyInput('mobile');
        }

        $request->session()->put('reset_mobile', $request->mobile);
        return $this->dispatchOtp($request->mobile, 'password_reset', route('password.reset.form'),
            'পাসওয়ার্ড রিসেট OTP পাঠানো হয়েছে।');
    }

    public function showResetPassword(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('reset_mobile')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', ['mobile' => $request->session()->get('reset_mobile')]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $mobile = $request->session()->get('reset_mobile');
        if (! $mobile) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'otp.required' => 'OTP কোড দিন',
            'password.required' => 'নতুন পাসওয়ার্ড দিন',
            'password.confirmed' => 'পাসওয়ার্ড মিলছে না',
        ]);

        if (! $this->sms->verifyOtp($mobile, $request->otp, 'password_reset')) {
            return back()->withErrors(['otp' => 'OTP কোড ভুল বা মেয়াদোত্তীর্ণ।']);
        }

        User::where('mobile', $mobile)->update(['password' => bcrypt($request->password)]);
        $request->session()->forget('reset_mobile');

        return redirect()->route('login')->with('success', 'পাসওয়ার্ড পরিবর্তন হয়েছে। এখন লগইন করুন।');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'সফলভাবে লগআউট হয়েছেন');
    }

    /* ---------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------- */

    protected function geoData(): array
    {
        return [
            'divisions' => Division::orderBy('bn_name')->get(['id', 'bn_name']),
            'districts' => District::active()->orderBy('bn_name')->get(['id', 'division_id', 'bn_name']),
            'upazilas' => Upazila::orderBy('bn_name')->get(['id', 'district_id', 'bn_name']),
        ];
    }

    protected function validateRegistration(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/', 'unique:users,mobile'],
            'division' => ['required', 'string', 'max:50'],
            'district' => ['required', 'string', 'max:50'],
            'upazila' => ['required', 'string', 'max:50'],
            'union_name' => ['nullable', 'string', 'max:80'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'আপনার নাম দিন',
            'mobile.required' => 'মোবাইল নম্বর প্রয়োজন',
            'mobile.regex' => 'সঠিক বাংলাদেশী মোবাইল নম্বর দিন',
            'mobile.unique' => 'এই মোবাইল নম্বর ইতিমধ্যে নিবন্ধিত',
            'division.required' => 'বিভাগ নির্বাচন করুন',
            'district.required' => 'জেলা নির্বাচন করুন',
            'upazila.required' => 'উপজেলা নির্বাচন করুন',
            'password.required' => 'পাসওয়ার্ড প্রয়োজন',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে',
            'password.confirmed' => 'পাসওয়ার্ড মিলছে না',
        ]);
    }

    protected function createUserAndLogin(array $data, Request $request): RedirectResponse
    {
        $user = User::create([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'division' => $data['division'],
            'district' => $data['district'],
            'upazila' => $data['upazila'],
            'union_name' => $data['union_name'] ?? null,
            'role' => 'farmer',
            'password' => $data['password'], // auto-hashed by cast
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'অ্যাকাউন্ট তৈরি হয়েছে! স্বাগতম ' . $user->name);
    }

    /** Send an OTP and redirect to the verify screen, surfacing the code in simulate mode. */
    protected function dispatchOtp(string $mobile, string $purpose, string $redirectTo, string $message): RedirectResponse
    {
        $res = $this->sms->generateOtp($mobile, $purpose);

        if (! $res['ok'] && $res['error']) {
            return back()->withErrors(['mobile' => $res['error']])->onlyInput('mobile');
        }

        if ($res['simulated'] && $res['otp']) {
            $message .= ' (ডেমো OTP: ' . $res['otp'] . ')';
        }

        return redirect($redirectTo)->with('success', $message);
    }
}
