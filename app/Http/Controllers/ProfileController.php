<?php

namespace App\Http\Controllers;

use App\Models\CropPost;
use App\Models\DiseaseScan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();

        return view('profile.index', [
            'user' => $user,
            'stats' => [
                'scans' => DiseaseScan::where('user_id', $user->id)->count(),
                'posts' => CropPost::where('user_id', $user->id)->active()->count(),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'নাম দিন',
        ]);

        Auth::user()->update($validated);

        return back()->with('success', 'প্রোফাইল আপডেট হয়েছে!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'বর্তমান পাসওয়ার্ড দিন',
            'current_password.current_password' => 'বর্তমান পাসওয়ার্ড ভুল',
            'password.required' => 'নতুন পাসওয়ার্ড দিন',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে',
            'password.confirmed' => 'পাসওয়ার্ড মিলছে না',
        ]);

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'পাসওয়ার্ড পরিবর্তিত হয়েছে!');
    }
}
