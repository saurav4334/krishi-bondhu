<?php

namespace App\Http\Controllers;

use App\Models\AiChatLog;
use App\Models\AiSetting;
use App\Models\UnansweredQuestion;
use App\Services\GeminiChatService;
use App\Services\KnowledgeBaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Hybrid chatbot: answers from the local Knowledge Base first (free, instant),
 * and only falls back to Gemini when nothing matches. Unanswered questions are
 * saved for admin review either way.
 */
class ChatController extends Controller
{
    public function __construct(
        protected KnowledgeBaseService $kb,
        protected GeminiChatService $ai,
    ) {
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ], [
            'message.required' => 'প্রশ্ন লিখুন',
            'message.max' => 'প্রশ্ন সর্বোচ্চ ৫০০ অক্ষরের হতে পারে',
        ]);

        $message = $validated['message'];
        $user = Auth::user();

        // ---- 1) Knowledge Base first (no API, no rate limit) ----
        $hit = $this->kb->search($message);
        if ($hit) {
            $this->kb->recordView($hit['article']);
            $this->log($user?->id, $request->ip(), $message, $hit['article']->answer, 'knowledge_base', null, 'success');

            return response()->json([
                'ok' => true,
                'source' => 'knowledge_base',
                'question' => $message,
                'answer' => $hit['article']->answer,
            ]);
        }

        // ---- KB miss → record for admin review ----
        UnansweredQuestion::create([
            'user_id' => $user?->id,
            'question' => $message,
            'district' => $user?->district,
            'phone' => $user?->mobile,
            'status' => 'pending',
        ]);

        $settings = AiSetting::current();

        // ---- 2) Gemini fallback (only if enabled) ----
        if ($settings->status) {
            // Daily limit applies ONLY to Gemini calls (KB stays unlimited).
            $limit = $user ? $settings->daily_limit : $settings->guest_limit;
            $usedToday = AiChatLog::whereDate('created_at', today())
                ->where('provider', 'gemini')
                ->when($user, fn ($q) => $q->where('user_id', $user->id), fn ($q) => $q->where('ip', $request->ip()))
                ->count();

            if ($usedToday >= $limit) {
                return response()->json([
                    'ok' => false,
                    'source' => 'limit',
                    'message' => "আজকের জন্য AI প্রশ্নের সীমা ({$limit}টি) শেষ হয়েছে। আগামীকাল আবার চেষ্টা করুন।",
                ], 429);
            }

            $result = $this->ai->ask($message);
            $this->log($user?->id, $request->ip(), $message, $result['answer'], 'gemini', $result['model'], $result['status'], $result['tokens']);

            return response()->json([
                'ok' => $result['status'] !== 'failed',
                'source' => 'gemini',
                'question' => $message,
                'answer' => $result['answer'],
            ]);
        }

        // ---- 3) Gemini disabled → expert fallback ----
        $fallback = 'আপনার প্রশ্নটি আমাদের বিশেষজ্ঞ দলের কাছে পাঠানো হয়েছে। শীঘ্রই উত্তর জানানো হবে। জরুরি প্রয়োজনে অ্যাপের "বিশেষজ্ঞ" বিভাগে যোগাযোগ করুন।';
        $this->log($user?->id, $request->ip(), $message, $fallback, 'unanswered', null, 'unanswered');

        return response()->json([
            'ok' => true,
            'source' => 'unanswered',
            'question' => $message,
            'answer' => $fallback,
        ]);
    }

    private function log(?int $userId, ?string $ip, string $question, ?string $answer, string $provider, ?string $model, string $status, ?int $tokens = null): void
    {
        AiChatLog::create([
            'user_id' => $userId,
            'ip' => $ip,
            'question' => $question,
            'answer' => $answer,
            'provider' => $provider,
            'model' => $model,
            'tokens_used' => $tokens,
            'status' => $status,
        ]);
    }
}
