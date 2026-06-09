<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Division;
use App\Models\Upazila;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
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
        return view('auth.register', [
            'divisions' => Division::orderBy('bn_name')->get(['id', 'bn_name']),
            'districts' => District::active()->orderBy('bn_name')->get(['id', 'division_id', 'bn_name']),
            'upazilas' => Upazila::orderBy('bn_name')->get(['id', 'district_id', 'bn_name']),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
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

        $user = User::create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'division' => $validated['division'],
            'district' => $validated['district'],
            'upazila' => $validated['upazila'],
            'union_name' => $validated['union_name'] ?? null,
            'role' => 'farmer',
            'password' => $validated['password'], // auto-hashed by cast
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'অ্যাকাউন্ট তৈরি হয়েছে! স্বাগতম ' . $user->name);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'সফলভাবে লগআউট হয়েছেন');
    }
}
