<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CropPost;
use App\Models\DiseaseScan;
use App\Models\Expert;
use App\Models\MarketPrice;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'stats' => [
                'total_users' => User::where('role', 'farmer')->count(),
                'total_experts' => User::where('role', 'expert')->count(),
                'total_scans' => DiseaseScan::count(),
                'active_posts' => CropPost::active()->count(),
                'directory_experts' => Expert::active()->count(),
                'notifications_sent' => Notification::count(),
                'total_prices' => MarketPrice::count(),
            ],
            'recent_scans' => DiseaseScan::with('user:id,name')->latest()->take(5)->get(),
            'recent_posts' => CropPost::with('user:id,name')->active()->latest()->take(5)->get(),
            'recent_users' => User::latest()->take(5)->get(),
        ]);
    }

    public function storeNotification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:1000'],
            'user_type' => ['required', 'in:all,farmer,expert,admin'],
        ], [
            'title.required' => 'শিরোনাম দিন',
            'message.required' => 'বার্তা দিন',
        ]);

        Notification::create($validated);

        return back()->with('success', 'বিজ্ঞপ্তি পাঠানো হয়েছে!');
    }

    public function storePrice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'crop_name' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:20'],
            'price' => ['required', 'numeric', 'min:0'],
        ], [
            'crop_name.required' => 'ফসলের নাম দিন',
            'price.required' => 'দাম দিন',
            'unit.required' => 'একক দিন',
        ]);

        MarketPrice::updateOrCreate(
            ['crop_name' => $validated['crop_name'], 'district' => $validated['district']],
            ['unit' => $validated['unit'], 'price' => $validated['price']],
        );

        return back()->with('success', 'বাজার দর আপডেট হয়েছে!');
    }

    public function deleteUser(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'অ্যাডমিন মুছে ফেলা যাবে না');
        }
        $user->delete();
        return back()->with('success', 'ব্যবহারকারী মুছে ফেলা হয়েছে');
    }
}
