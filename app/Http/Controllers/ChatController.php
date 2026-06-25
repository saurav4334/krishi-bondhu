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
        $ip = $request->ip();

        // Daily limit + today's Gemini usage — read live from AiSetting (no hardcoding).
        $settings = AiSetting::current();
        $limit = $user ? $settings->daily_limit : $settings->guest_limit;
        $used = AiChatLog::geminiUsedToday($user?->id, $ip);
        $usage = fn (int $u) => ['used' => $u, 'limit' => $limit];

        // ---- 1) Knowledge Base first — NEVER consumes the Gemini limit ----
        $hit = $this->kb->search($message);
        if ($hit) {
            $this->kb->recordView($hit['article']);
            $this->log($user?->id, $ip, $message, $hit['article']->answer, 'knowledge_base', null, 'success');

            return response()->json([
                'ok' => true,
                'source' => 'knowledge_base',
                'source_label' => $hit['article']->source_label,
                'source_url' => $hit['article']->source_url,
                'question' => $message,
                'answer' => $hit['article']->answer,
                'usage' => $usage($used), // unchanged — KB is free
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

        // ---- 2) Gemini fallback (only if enabled) ----
        if ($settings->status) {
            if ($used >= $limit) {
                return response()->json([
                    'ok' => false,
                    'source' => 'limit',
                    'message' => "আজকের জন্য AI প্রশ্নের সীমা ({$limit}টি) শেষ হয়েছে। আগামীকাল আবার চেষ্টা করুন।",
                    'usage' => $usage($used),
                ], 429);
            }

            $result = $this->ai->ask($message);
            // A Gemini call was made → it consumes exactly one from today's counter.
            $this->log($user?->id, $ip, $message, $result['answer'], 'gemini', $result['model'], $result['status'], $result['tokens']);

            return response()->json([
                'ok' => $result['status'] !== 'failed',
                'source' => 'gemini',
                'source_label' => $result['status'] === 'success' ? 'কৃষি AI (Gemini)' : null,
                'question' => $message,
                'answer' => $result['answer'],
                'usage' => $usage($used + 1),
            ]);
        }

        // ---- 3) Gemini disabled → expert fallback (no limit consumed) ----
        $fallback = 'আপনার প্রশ্নটি আমাদের বিশেষজ্ঞ দলের কাছে পাঠানো হয়েছে। শীঘ্রই উত্তর জানানো হবে। জরুরি প্রয়োজনে অ্যাপের "বিশেষজ্ঞ" বিভাগে যোগাযোগ করুন।';
        $this->log($user?->id, $ip, $message, $fallback, 'unanswered', null, 'unanswered');

        return response()->json([
            'ok' => true,
            'source' => 'unanswered',
            'question' => $message,
            'answer' => $fallback,
            'usage' => $usage($used),
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
