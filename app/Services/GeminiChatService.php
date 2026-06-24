<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * কৃষি AI সহকারী — Gemini-backed agricultural Q&A in Bengali.
 *
 * Resilient model handling:
 *  - Tries the configured model (default gemini-2.5-flash); on "model not found"
 *    (404) it automatically falls back through gemini-2.0-flash, gemini-1.5-pro,
 *    gemini-1.5-flash.
 *  - The first model that works is cached for 24h and tried first next time.
 *  - Returns detailed Bengali errors (invalid key / model not found / rate limit
 *    / timeout) instead of one generic message.
 *
 * Shared-hosting friendly: one synchronous call per model (20s timeout), no
 * queue. The API key never leaves the backend.
 */
class GeminiChatService
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private const CACHE_KEY = 'gemini_working_model';
    private const CACHE_TTL = 86400; // 24 hours

    /** Fallback chain after the configured default model. */
    private const FALLBACKS = ['gemini-2.0-flash', 'gemini-1.5-pro', 'gemini-1.5-flash'];

    public const SAFETY_NOTE = 'নির্দিষ্ট মাত্রা ও প্রয়োগের আগে স্থানীয় কৃষি কর্মকর্তার পরামর্শ নিন।';

    // Detailed Bengali error messages
    public const ERR_NO_KEY = 'API Key সেট করা নেই। সার্ভারে GEMINI_API_KEY যোগ করুন।';
    public const ERR_INVALID_KEY = 'API Key সঠিক নয় বা অনুমোদিত নয়। সঠিক GEMINI_API_KEY দিন।';
    public const ERR_MODEL_NOT_FOUND = 'নির্ধারিত Gemini মডেল পাওয়া যায়নি। কোনো বিকল্প মডেলও কাজ করেনি।';
    public const ERR_RATE_LIMIT = 'অনুরোধের সীমা (rate limit) অতিক্রম হয়েছে। কিছুক্ষণ পরে আবার চেষ্টা করুন।';
    public const ERR_TIMEOUT = 'সংযোগে সময় শেষ হয়েছে (timeout)। ইন্টারনেট বা সার্ভার সংযোগ পরীক্ষা করুন।';
    public const ERR_GENERIC = 'দুঃখিত, এখন উত্তর দেওয়া যাচ্ছে না। কিছুক্ষণ পরে আবার চেষ্টা করুন।';

    /**
     * Ask an agriculture question.
     *
     * @return array{answer:string, status:string, model:?string, tokens:?int, error:?string}
     */
    public function ask(string $question): array
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            return [
                'answer' => $this->simulatedAnswer($question),
                'status' => 'simulated',
                'model' => $this->configuredModel(),
                'tokens' => null,
                'error' => null,
            ];
        }

        $sawModelNotFound = false;

        foreach ($this->modelChain() as $model) {
            try {
                $response = Http::timeout(20)->post(
                    self::ENDPOINT . $model . ':generateContent?key=' . $apiKey,
                    [
                        'systemInstruction' => ['parts' => [['text' => $this->systemPrompt()]]],
                        'contents' => [['role' => 'user', 'parts' => [['text' => $question]]]],
                        'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 800],
                    ]
                );
            } catch (ConnectionException $e) {
                Log::error("Gemini timeout on {$model}: " . $e->getMessage());

                return $this->fail(self::ERR_TIMEOUT, $model, 'timeout');
            } catch (\Throwable $e) {
                Log::error("Gemini error on {$model}: " . $e->getMessage());
                continue;
            }

            if ($response->successful()) {
                $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
                if (empty($text)) {
                    continue;
                }

                Cache::put(self::CACHE_KEY, $model, self::CACHE_TTL); // remember the working model

                return [
                    'answer' => $this->withSafetyNote($question, trim($text)),
                    'status' => 'success',
                    'model' => $model,
                    'tokens' => data_get($response->json(), 'usageMetadata.totalTokenCount'),
                    'error' => null,
                ];
            }

            // Classify the failure.
            $status = $response->status();
            $body = $response->body();
            Log::warning("Gemini {$model} HTTP {$status}: " . mb_substr($body, 0, 300));

            if ($status === 404 || str_contains($body, 'NOT_FOUND') || str_contains($body, 'is not found')) {
                $sawModelNotFound = true;
                Cache::forget(self::CACHE_KEY); // cached model no longer valid — keep trying others
                continue;
            }
            if ($status === 429 || str_contains($body, 'RESOURCE_EXHAUSTED')) {
                return $this->fail(self::ERR_RATE_LIMIT, $model, 'rate_limit');
            }
            if ($status === 401 || $status === 403 || str_contains($body, 'API_KEY_INVALID') || str_contains($body, 'API key not valid')) {
                return $this->fail(self::ERR_INVALID_KEY, $model, 'invalid_key');
            }
            // Any other error: try the next model.
        }

        return $this->fail(
            $sawModelNotFound ? self::ERR_MODEL_NOT_FOUND : self::ERR_GENERIC,
            $this->configuredModel(),
            $sawModelNotFound ? 'model_not_found' : 'failed'
        );
    }

    /** Health check used by the admin "Test Gemini Connection" button. */
    public function testConnection(): array
    {
        if (empty(config('services.gemini.api_key'))) {
            return ['ok' => false, 'model' => null, 'message' => '⚠️ ' . self::ERR_NO_KEY];
        }

        $result = $this->ask('সংক্ষেপে এক বাক্যে বলুন: ধান চাষের একটি পরামর্শ।');
        $ok = $result['status'] === 'success';

        return [
            'ok' => $ok,
            'model' => $result['model'],
            'message' => $ok
                ? ('✅ সংযোগ সফল! সক্রিয় মডেল: ' . $result['model'])
                : ('❌ ' . $result['answer']),
        ];
    }

    /** The model currently in use (cached working model, else configured default). */
    public function currentModel(): string
    {
        return Cache::get(self::CACHE_KEY) ?: $this->configuredModel();
    }

    private function configuredModel(): string
    {
        return config('services.gemini.model', 'gemini-2.5-flash');
    }

    /** Ordered, de-duplicated list: cached working model, configured default, fallbacks. */
    private function modelChain(): array
    {
        $chain = array_merge([Cache::get(self::CACHE_KEY), $this->configuredModel()], self::FALLBACKS);

        return array_values(array_unique(array_filter($chain)));
    }

    private function fail(string $message, ?string $model, string $code): array
    {
        return ['answer' => $message, 'status' => 'failed', 'model' => $model, 'tokens' => null, 'error' => $code];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
আপনি "কৃষি-বন্ধু" অ্যাপের একজন অভিজ্ঞ কৃষি সহকারী। বাংলাদেশের কৃষকদের জন্য সবসময় বাংলায়, সহজ ও ব্যবহারিক ভাষায় পরামর্শ দিন।

নিয়মাবলি:
- প্রতিটি উত্তর অবশ্যই বাংলায় দিন এবং কৃষি প্রসঙ্গে সীমাবদ্ধ রাখুন।
- ধাপে ধাপে ব্যবহারিক সমাধান দিন।
- কীটনাশক, সার, রাসায়নিক বা প্রাণিসম্পদ সম্পর্কিত উত্তরে অবশ্যই নিরাপত্তা সতর্কতা যোগ করুন: "নির্দিষ্ট মাত্রা ও প্রয়োগের আগে স্থানীয় কৃষি কর্মকর্তার পরামর্শ নিন।"
- গুরুতর সমস্যায় স্থানীয় কৃষি বিশেষজ্ঞ/কর্মকর্তার সাথে যোগাযোগের পরামর্শ দিন।
- কোনো নিশ্চিত গ্যারান্টি বা অতিরিক্ত মাত্রার ওষুধ/রাসায়নিক পরামর্শ দেবেন না।
- শুধুমাত্র কৃষি, ফসল, সার, বীজ, কীটনাশক, সেচ, যন্ত্রপাতি, বাজার দর ও প্রাণিসম্পদ সম্পর্কিত প্রশ্নের উত্তর দিন। অপ্রাসঙ্গিক প্রশ্নে নম্রভাবে জানান যে আপনি শুধু কৃষি বিষয়ে সাহায্য করতে পারেন।
- উত্তর সংক্ষিপ্ত ও স্পষ্ট রাখুন।
PROMPT;
    }

    /** Guarantee the safety note for sensitive topics, even if the model omits it. */
    private function withSafetyNote(string $question, string $answer): string
    {
        if (str_contains($answer, self::SAFETY_NOTE)) {
            return $answer;
        }

        $sensitive = ['কীটনাশক', 'সার', 'রোগ', 'পোকা', 'ওষুধ', 'মাত্রা', 'স্প্রে', 'রাসায়নিক', 'ছত্রাক', 'পশু', 'গবাদি', 'মুরগি', 'fungicide', 'pesticide'];
        $haystack = $question . ' ' . $answer;
        foreach ($sensitive as $word) {
            if (mb_stripos($haystack, $word) !== false) {
                return $answer . "\n\n⚠️ " . self::SAFETY_NOTE;
            }
        }

        return $answer;
    }

    /** Safe canned reply when the module has no API key (dev/demo). */
    private function simulatedAnswer(string $question): string
    {
        return "আপনার প্রশ্ন: \"" . mb_strimwidth($question, 0, 80, '…') . "\"\n\n"
            . "এই মুহূর্তে AI সেবাটি ডেমো মোডে চলছে। সঠিক ও বিস্তারিত পরামর্শের জন্য আপনার নিকটস্থ কৃষি অফিস বা কৃষি বিশেষজ্ঞের সাথে যোগাযোগ করুন। "
            . "অ্যাপের 'বিশেষজ্ঞ' বিভাগ থেকেও সরাসরি পরামর্শ নিতে পারেন।\n\n⚠️ " . self::SAFETY_NOTE;
    }
}
