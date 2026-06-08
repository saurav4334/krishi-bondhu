<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $userType = Auth::user()->role;

        $notifications = Notification::where(function ($q) use ($userType) {
                $q->where('user_type', 'all')->orWhere('user_type', $userType);
            })
            ->latest()
            ->paginate(30);

        return view('notifications.index', compact('notifications'));
    }
}
