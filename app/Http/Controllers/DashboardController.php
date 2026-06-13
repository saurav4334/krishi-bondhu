<?php

namespace App\Http\Controllers;

use App\Models\CropPost;
use App\Models\DiseaseScan;
use App\Models\MarketPrice;
use App\Models\NewsPost;
use App\Models\WeatherAlert;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected WeatherService $weatherService)
    {
    }

    public function index(): View
    {
        $user = Auth::user();

        $weatherAlerts = WeatherAlert::activeFor($user->district ?: 'ঢাকা')
            ->orderBy('alert_date')
            ->take(3)
            ->get();

        $latestNews = NewsPost::published()
            ->with('category')
            ->where(fn ($q) => $q->whereNull('district')->orWhere('district', $user->district))
            ->orderByDesc('is_important')
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'weather' => $this->weatherService->getWeather($user->district),
            'weatherAlerts' => $weatherAlerts,
            'latestNews' => $latestNews,
            'stats' => [
                'scans' => DiseaseScan::where('user_id', $user->id)->count(),
                'posts' => CropPost::where('user_id', $user->id)->active()->count(),
                'prices' => MarketPrice::count(),
            ],
            'recent_scans' => DiseaseScan::where('user_id', $user->id)
                ->latest()
                ->take(3)
                ->get(),
            'tips' => [
                ['icon' => '🌱', 'title' => 'সঠিক সময়ে সেচ দিন', 'body' => 'ধান চাষে ফুল আসার সময় পানির অভাব হলে ফলন ৪০% পর্যন্ত কমতে পারে।'],
                ['icon' => '🐛', 'title' => 'পোকা দমনে সতর্ক থাকুন', 'body' => 'মাজরা পোকার আক্রমণ থেকে ধান রক্ষায় ফেরোমন ফাঁদ ব্যবহার করুন।'],
                ['icon' => '💧', 'title' => 'জৈব সার ব্যবহার করুন', 'body' => 'রাসায়নিক সারের পাশাপাশি জৈব সার ব্যবহারে মাটির স্বাস্থ্য ভালো থাকে।'],
            ],
        ]);
    }
}
