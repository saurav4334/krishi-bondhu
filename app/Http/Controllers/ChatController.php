<?php

namespace App\Http\Controllers;

use App\Models\AiChatLog;
use App\Models\AiSetting;
use App\Services\GeminiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct(protected GeminiChatService $ai)
    {
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ], [
            'message.required' => 'প্রশ্ন লিখুন',
            'message.max' => 'প্রশ্ন সর্বোচ্চ ৫০০ অক্ষরের হতে পারে',
        ]);

        $settings = AiSetting::current();
        if (! $settings->status) {
            return response()->json(['ok' => false, 'message' => 'AI সহকারী এই মুহূর্তে বন্ধ আছে।'], 503);
        }

        // Daily rate limit: per logged-in user, else per guest IP.
        $userId = Auth::id();
        $limit = $userId ? $settings->daily_limit : $settings->guest_limit;
        $usedToday = AiChatLog::whereDate('created_at', today())
            ->when($userId, fn ($q) => $q->where('user_id', $userId), fn ($q) => $q->where('ip', $request->ip()))
            ->count();

        if ($usedToday >= $limit) {
            return response()->json([
                'ok' => false,
                'message' => "আজকের জন্য প্রশ্নের সীমা ({$limit}টি) শেষ হয়েছে। আগামীকাল আবার চেষ্টা করুন।",
            ], 429);
        }

        $result = $this->ai->ask($validated['message']);

        AiChatLog::create([
            'user_id' => $userId,
            'ip' => $request->ip(),
            'question' => $validated['message'],
            'answer' => $result['answer'],
            'provider' => 'gemini',
            'model' => $result['model'],
            'tokens_used' => $result['tokens'],
            'status' => $result['status'],
        ]);

        return response()->json([
            'ok' => $result['status'] !== 'failed',
            'answer' => $result['answer'],
            'remaining' => max(0, $limit - $usedToday - 1),
        ]);
    }
}
