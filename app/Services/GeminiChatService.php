<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * কৃষি AI সহকারী — Gemini-backed agricultural Q&A in Bengali.
 *
 * Shared-hosting friendly: a single synchronous HTTP call (20s timeout), no
 * queue. Returns a structured result the controller logs. When no API key is
 * configured it falls back to a safe simulated reply so the feature still works
 * in dev/demo. The API key never leaves the backend.
 */
class GeminiChatService
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public const SAFETY_NOTE = 'নির্দিষ্ট মাত্রা ও প্রয়োগের আগে স্থানীয় কৃষি কর্মকর্তার পরামর্শ নিন।';

    public const ERROR_MESSAGE = 'দুঃখিত, এখন উত্তর দেওয়া যাচ্ছে না। কিছুক্ষণ পরে আবার চেষ্টা করুন।';

    /**
     * Ask an agriculture question.
     *
     * @return array{answer:string, status:string, model:?string, tokens:?int}
     */
    public function ask(string $question): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-1.5-flash');

        if (empty($apiKey)) {
            return [
                'answer' => $this->simulatedAnswer($question),
                'status' => 'simulated',
                'model' => $model,
                'tokens' => null,
            ];
        }

        try {
            $response = Http::timeout(20)
                ->post(self::ENDPOINT . $model . ':generateContent?key=' . $apiKey, [
                    'systemInstruction' => ['parts' => [['text' => $this->systemPrompt()]]],
                    'contents' => [['role' => 'user', 'parts' => [['text' => $question]]]],
                    'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 800],
                ]);

            if (! $response->successful()) {
                Log::warning('Gemini chat HTTP ' . $response->status() . ': ' . $response->body());

                return ['answer' => self::ERROR_MESSAGE, 'status' => 'failed', 'model' => $model, 'tokens' => null];
            }

            $data = $response->json();
            $text = data_get($data, 'candidates.0.content.parts.0.text');

            if (empty($text)) {
                return ['answer' => self::ERROR_MESSAGE, 'status' => 'failed', 'model' => $model, 'tokens' => null];
            }

            return [
                'answer' => $this->withSafetyNote($question, trim($text)),
                'status' => 'success',
                'model' => $model,
                'tokens' => data_get($data, 'usageMetadata.totalTokenCount'),
            ];
        } catch (\Throwable $e) {
            Log::error('Gemini chat failed: ' . $e->getMessage());

            return ['answer' => self::ERROR_MESSAGE, 'status' => 'failed', 'model' => $model, 'tokens' => null];
        }
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
আপনি "কৃষি-বন্ধু" অ্যাপের একজন অভিজ্ঞ কৃষি সহকারী। বাংলাদেশের কৃষকদের জন্য সহজ, ব্যবহারিক বাংলায় পরামর্শ দিন।

নিয়মাবলি:
- সবসময় বাংলায় উত্তর দিন, সহজ ভাষায়।
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
