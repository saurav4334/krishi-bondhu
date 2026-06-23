<?php

namespace App\Services;

use App\Models\User;
use App\Models\VoiceCallbackRequest;
use App\Models\VoiceCallLog;
use App\Models\VoiceSetting;
use App\Models\VoiceTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Protiddhoni Direct-TTS + IVR voice automation.
 *
 * Design notes for shared cPanel hosting:
 *  - No queue workers. Calls are written to voice_call_logs as `queued` and
 *    flushed in small batches by a cron command (voice:dispatch).
 *  - Every request payload + API response is persisted for audit/retry.
 *  - When the module is disabled/unconfigured, calls are recorded as
 *    `simulated` (never hitting the API) so the rest of the app keeps working.
 *  - The API token is read from encrypted settings and only ever sent in the
 *    Authorization header — never stored in payloads or exposed to the frontend.
 */
class ProtiddhoniVoiceService
{
    /** How many calls a single cron tick will push to the API. */
    public const BATCH_SIZE = 25;

    /** DTMF keys (per feature) that create a follow-up VoiceCallbackRequest. */
    protected array $actionKeys = [
        'weather_alert' => ['2'],                  // expert callback requested
        'crop_lead_confirmation' => ['1', '3'],    // send buyer info / contact later
        'equipment_rental_confirmation' => ['1', '2'], // booking confirmed / cancelled
        'labor_match' => ['1', '3'],               // interested / details later
        'government_circular' => ['2'],            // callback for application help
        'expert_callback' => ['1'],                // confirm expert callback
        'market_price_alert' => [],                // informational only
        'equipment_inquiry' => ['1', '3'],         // send buyer info / contact later
    ];

    // ---------------------------------------------------------------------
    // Public feature methods (one per PRD use-case)
    // ---------------------------------------------------------------------

    public function sendWeatherAlert(string $phone, array $vars = [], ?int $userId = null, $relatedId = null, bool $immediate = false): VoiceCallLog
    {
        return $this->dispatch('weather_alert', $phone, $vars, $userId, $relatedId, $immediate);
    }

    public function sendCropLeadConfirmation(string $phone, array $vars = [], ?int $userId = null, $relatedId = null, bool $immediate = false): VoiceCallLog
    {
        return $this->dispatch('crop_lead_confirmation', $phone, $vars, $userId, $relatedId, $immediate);
    }

    public function sendEquipmentRentalConfirmation(string $phone, array $vars = [], ?int $userId = null, $relatedId = null, bool $immediate = false): VoiceCallLog
    {
        return $this->dispatch('equipment_rental_confirmation', $phone, $vars, $userId, $relatedId, $immediate);
    }

    public function sendLaborMatchCall(string $phone, array $vars = [], ?int $userId = null, $relatedId = null, bool $immediate = false): VoiceCallLog
    {
        return $this->dispatch('labor_match', $phone, $vars, $userId, $relatedId, $immediate);
    }

    public function sendGovernmentCircularAlert(string $phone, array $vars = [], ?int $userId = null, $relatedId = null, bool $immediate = false): VoiceCallLog
    {
        return $this->dispatch('government_circular', $phone, $vars, $userId, $relatedId, $immediate);
    }

    public function sendExpertCallback(string $phone, array $vars = [], ?int $userId = null, $relatedId = null, bool $immediate = false): VoiceCallLog
    {
        return $this->dispatch('expert_callback', $phone, $vars, $userId, $relatedId, $immediate);
    }

    public function sendMarketPriceAlert(string $phone, array $vars = [], ?int $userId = null, $relatedId = null, bool $immediate = false): VoiceCallLog
    {
        return $this->dispatch('market_price_alert', $phone, $vars, $userId, $relatedId, $immediate);
    }

    public function sendEquipmentInquiry(string $phone, array $vars = [], ?int $userId = null, $relatedId = null, bool $immediate = false): VoiceCallLog
    {
        return $this->dispatch('equipment_inquiry', $phone, $vars, $userId, $relatedId, $immediate);
    }

    /** Admin test call — fires immediately and returns the log. */
    public function sendTestCall(string $phone, string $feature = 'weather_alert', array $vars = []): VoiceCallLog
    {
        $vars = array_merge(['name' => 'কৃষক ভাই', 'district' => 'বাংলাদেশ', 'date' => now()->format('d/m/Y')], $vars);

        return $this->dispatch($feature, $phone, $vars, null, null, true);
    }

    /**
     * Queue a feature call for every farmer in a district (weather / circular
     * campaigns). Returns the number of calls queued — cron sends them.
     */
    public function dispatchToFarmersInDistrict(string $feature, string $district, array $extraVars = [], $relatedId = null): int
    {
        $count = 0;

        User::where('role', 'farmer')->where('district', $district)
            ->whereNotNull('mobile')
            ->select('id', 'name', 'mobile', 'district')
            ->chunk(200, function ($chunk) use ($feature, $extraVars, $relatedId, &$count) {
                foreach ($chunk as $u) {
                    $vars = array_merge(['name' => $u->name, 'district' => $u->district], $extraVars);
                    $this->queueCall($feature, $u->mobile, $vars, $u->id, $relatedId);
                    $count++;
                }
            });

        return $count;
    }

    // ---------------------------------------------------------------------
    // Dispatch / send pipeline
    // ---------------------------------------------------------------------

    protected function dispatch(string $feature, string $phone, array $vars, ?int $userId, $relatedId, bool $immediate): VoiceCallLog
    {
        $log = $this->queueCall($feature, $phone, $vars, $userId, $relatedId);

        if ($immediate) {
            $this->sendLog($log);
        }

        return $log;
    }

    /** Build the API payload from the feature template and persist as `queued`. */
    public function queueCall(string $feature, string $phone, array $vars, ?int $userId = null, $relatedId = null): VoiceCallLog
    {
        $settings = VoiceSetting::current();
        $tpl = $this->template($feature);
        $requestId = (string) Str::uuid();

        $body = [
            'request_id' => $requestId,
            'sender' => $settings->sender ?: config('services.protiddhoni.sender'),
            'phone_numbers' => [$phone],
            'voice' => ($tpl['voice_type'] ?? null) ?: ($settings->default_voice ?: 'female'),
            'language_code' => ($tpl['language_code'] ?? null) ?: ($settings->language_code ?: 'bn'),
            'start_texts' => array_values(array_filter([$this->render($tpl['start_text'] ?? '', $vars)])),
            'question_texts' => [$this->render($tpl['question_text'] ?? '', $vars)],
            'end_texts' => array_values(array_filter([$this->render($tpl['end_text'] ?? '', $vars)])),
            'metadata' => ['feature' => $feature, 'related_id' => $relatedId],
            'dtmf_options' => $this->renderDtmf($tpl['dtmf_options'] ?? [], $vars),
        ];

        return VoiceCallLog::create([
            'user_id' => $userId,
            'phone' => $phone,
            'feature_type' => $feature,
            'related_id' => $relatedId,
            'request_id' => $requestId,
            'payload' => json_encode($body, JSON_UNESCAPED_UNICODE),
            'call_status' => 'queued',
        ]);
    }

    /** POST a queued log to the Protiddhoni API. Returns the resulting status. */
    public function sendLog(VoiceCallLog $log): string
    {
        $settings = VoiceSetting::current();

        if (! $settings->isEnabled()) {
            $log->update([
                'call_status' => 'simulated',
                'api_response' => 'ভয়েস মডিউল নিষ্ক্রিয়/অসম্পূর্ণ — অনুরোধ শুধু লগ করা হয়েছে।',
            ]);

            return 'simulated';
        }

        $body = json_decode($log->payload, true) ?: [];

        try {
            $response = Http::withToken($settings->api_token)
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post($settings->api_base_url, $body);

            $ok = $response->successful();
            $log->update([
                'call_status' => $ok ? 'sent' : 'failed',
                'api_response' => mb_substr($response->body(), 0, 5000),
                'retry_count' => $ok ? $log->retry_count : $log->retry_count + 1,
            ]);

            return $ok ? 'sent' : 'failed';
        } catch (\Throwable $e) {
            Log::error('Protiddhoni voice send failed: ' . $e->getMessage());
            $log->update([
                'call_status' => 'failed',
                'api_response' => mb_substr($e->getMessage(), 0, 5000),
                'retry_count' => $log->retry_count + 1,
            ]);

            return 'failed';
        }
    }

    /** Cron entry point: push a small batch of queued/retryable calls. */
    public function processBatch(int $limit = self::BATCH_SIZE): int
    {
        $sent = 0;

        VoiceCallLog::dispatchable()->orderBy('id')->limit($limit)->get()
            ->each(function (VoiceCallLog $log) use (&$sent) {
                $this->sendLog($log);
                $sent++;
            });

        return $sent;
    }

    // ---------------------------------------------------------------------
    // DTMF capture (from the public callback webhook)
    // ---------------------------------------------------------------------

    /** Record a keypad response against its call; queue follow-up if actionable. */
    public function recordDtmf(string $requestId, ?string $key): ?VoiceCallLog
    {
        $log = VoiceCallLog::where('request_id', $requestId)->first();
        if (! $log) {
            return null;
        }

        $log->update(['dtmf_key' => $key]);

        if ($key !== null && in_array($key, $this->actionKeys[$log->feature_type] ?? [], true)) {
            VoiceCallbackRequest::create([
                'user_id' => $log->user_id,
                'feature_type' => $log->feature_type,
                'related_id' => $log->related_id,
                'phone' => $log->phone,
                'status' => 'pending',
            ]);
        }

        return $log;
    }

    // ---------------------------------------------------------------------
    // Templates + variable rendering
    // ---------------------------------------------------------------------

    /** Returns a template array for a feature (DB row, else built-in default). */
    public function template(string $feature): array
    {
        $row = VoiceTemplate::forType($feature);
        if ($row) {
            return [
                'start_text' => $row->start_text,
                'question_text' => $row->question_text,
                'end_text' => $row->end_text,
                'dtmf_options' => $row->dtmf_options ?? [],
                'voice_type' => $row->voice_type,
                'language_code' => $row->language_code,
            ];
        }

        return self::DEFAULTS[$feature] ?? self::DEFAULTS['weather_alert'];
    }

    /** Replace {{var}} placeholders; strip any left unfilled. */
    public function render(?string $text, array $vars): string
    {
        $text = (string) $text;
        foreach ($vars as $key => $value) {
            $text = str_replace('{{' . $key . '}}', (string) $value, $text);
        }

        return trim(preg_replace('/\{\{\s*\w+\s*\}\}/u', '', $text));
    }

    protected function renderDtmf(array $options, array $vars): array
    {
        return array_map(function ($o) use ($vars) {
            return [
                'key' => (string) ($o['key'] ?? ''),
                'option_type' => $o['option_type'] ?? 'voice',
                'texts' => array_map(fn ($t) => $this->render($t, $vars), $o['texts'] ?? []),
            ];
        }, $options);
    }

    /**
     * Built-in default Bengali templates (also seeded into voice_templates).
     * Keeping them here means the module works even before seeding/editing.
     */
    public const DEFAULTS = [
        'weather_alert' => [
            'title' => 'স্মার্ট আবহাওয়া সতর্কতা',
            'start_text' => 'হ্যালো {{name}}, আপনি {{district}} এলাকার কৃষি-বন্ধু ব্যবহারকারী।',
            'question_text' => 'আপনার এলাকায় আবহাওয়া সতর্কতা রয়েছে। নিরাপত্তা পরামর্শ শুনতে ১ চাপুন, কৃষি বিশেষজ্ঞের কলব্যাক চাইলে ২ চাপুন, পরে শুনতে চাইলে ৩ চাপুন।',
            'end_text' => 'ধন্যবাদ। কৃষি-বন্ধুর সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['আগামী ২৪ ঘণ্টা ফসল নিরাপদ স্থানে রাখুন, জমির পানি নিষ্কাশনের ব্যবস্থা করুন এবং ঝড়-বৃষ্টির সময় জমিতে কাজ করা থেকে বিরত থাকুন।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['আপনার কৃষি বিশেষজ্ঞ কলব্যাক অনুরোধ গ্রহণ করা হয়েছে। আমাদের প্রতিনিধি দ্রুত যোগাযোগ করবে।']],
                ['key' => '3', 'option_type' => 'voice', 'texts' => ['ঠিক আছে। আমরা আপনাকে পরে আবার গুরুত্বপূর্ণ সতর্কতা জানাবো।']],
            ],
        ],
        'crop_lead_confirmation' => [
            'title' => 'ফসল বিক্রয় ক্রেতা আগ্রহ নিশ্চিতকরণ',
            'start_text' => 'হ্যালো {{name}}, আপনার {{crop}} বিক্রয় বিজ্ঞাপনে একজন ক্রেতা আগ্রহ দেখিয়েছেন।',
            'question_text' => 'ক্রেতার তথ্য পেতে ১ চাপুন, এই আগ্রহ বাতিল করতে ২ চাপুন, পরে যোগাযোগ করতে চাইলে ৩ চাপুন।',
            'end_text' => 'ধন্যবাদ। কৃষি-বন্ধু আপনার ফসল বিক্রয়ে সহায়তা করবে।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['আপনার অনুরোধ গ্রহণ করা হয়েছে। ক্রেতার যোগাযোগ তথ্য আপনাকে অ্যাপ নোটিফিকেশন বা এস এম এস এর মাধ্যমে পাঠানো হবে।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['ঠিক আছে। এই ক্রেতার আগ্রহ বাতিল হিসেবে সংরক্ষণ করা হয়েছে।']],
                ['key' => '3', 'option_type' => 'voice', 'texts' => ['ঠিক আছে। পরে যোগাযোগ করার জন্য অনুরোধটি সংরক্ষণ করা হয়েছে।']],
            ],
        ],
        'equipment_rental_confirmation' => [
            'title' => 'কৃষি সরঞ্জাম বুকিং নিশ্চিতকরণ',
            'start_text' => 'হ্যালো {{name}}, আপনার {{product}} এর জন্য একটি বুকিং অনুরোধ এসেছে।',
            'question_text' => 'বুকিং কনফার্ম করতে ১ চাপুন, বুকিং বাতিল করতে ২ চাপুন, বুকিং সম্পর্কে পরে সিদ্ধান্ত নিতে ৩ চাপুন।',
            'end_text' => 'ধন্যবাদ। কৃষি সরঞ্জাম সেবায় কৃষি-বন্ধুর সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['আপনার বুকিং কনফার্ম করা হয়েছে। বুকিং তথ্য সংশ্লিষ্ট ব্যবহারকারীকে জানিয়ে দেওয়া হবে।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['আপনার বুকিং বাতিল করা হয়েছে। আমরা সংশ্লিষ্ট ব্যবহারকারীকে জানিয়ে দেব।']],
                ['key' => '3', 'option_type' => 'voice', 'texts' => ['ঠিক আছে। বুকিংটি অপেক্ষমান অবস্থায় রাখা হয়েছে।']],
            ],
        ],
        'labor_match' => [
            'title' => 'কৃষি শ্রমিক কাজের ম্যাচিং',
            'start_text' => 'হ্যালো {{name}}, আপনার এলাকায় একটি নতুন কৃষি শ্রমিক কাজ পাওয়া গেছে।',
            'question_text' => 'এই কাজের জন্য আগ্রহী হলে ১ চাপুন, আগ্রহী না হলে ২ চাপুন, বিস্তারিত পরে জানতে চাইলে ৩ চাপুন।',
            'end_text' => 'ধন্যবাদ। কৃষি-বন্ধু শ্রমিক সেবার সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['আপনার আগ্রহ গ্রহণ করা হয়েছে। কাজের মালিককে আপনার তথ্য জানিয়ে দেওয়া হবে।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['ঠিক আছে। এই কাজের জন্য আপনাকে আগ্রহী হিসেবে ধরা হবে না।']],
                ['key' => '3', 'option_type' => 'voice', 'texts' => ['ঠিক আছে। কাজের বিস্তারিত পরে জানার অনুরোধ সংরক্ষণ করা হয়েছে।']],
            ],
        ],
        'government_circular' => [
            'title' => 'সরকারি কৃষি বিজ্ঞপ্তি ভয়েস অ্যালার্ট',
            'start_text' => 'হ্যালো {{name}}, কৃষি-বন্ধু থেকে একটি গুরুত্বপূর্ণ সরকারি কৃষি বিজ্ঞপ্তি রয়েছে।',
            'question_text' => 'বিজ্ঞপ্তির বিস্তারিত শুনতে ১ চাপুন, আবেদন বা সহায়তার জন্য কলব্যাক চাইলে ২ চাপুন, পরে শুনতে চাইলে ৩ চাপুন।',
            'end_text' => 'ধন্যবাদ। সরকারি কৃষি তথ্য পেতে কৃষি-বন্ধুর সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['সরকারি কৃষি সহায়তা বা বিজ্ঞপ্তির বিস্তারিত জানতে আপনার নিকটস্থ কৃষি অফিসে যোগাযোগ করুন অথবা কৃষি-বন্ধু অ্যাপের কৃষি সংবাদ বিভাগ দেখুন।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['আপনার কলব্যাক অনুরোধ গ্রহণ করা হয়েছে। আমাদের প্রতিনিধি বিস্তারিত তথ্য জানাতে যোগাযোগ করবে।']],
                ['key' => '3', 'option_type' => 'voice', 'texts' => ['ঠিক আছে। আমরা আপনাকে পরে আবার এই গুরুত্বপূর্ণ বিজ্ঞপ্তি জানাবো।']],
            ],
        ],
        'expert_callback' => [
            'title' => 'কৃষি বিশেষজ্ঞ কলব্যাক অনুরোধ',
            'start_text' => 'হ্যালো {{name}}, আপনার কৃষি বিশেষজ্ঞ সহায়তা অনুরোধ গ্রহণ করা হয়েছে।',
            'question_text' => 'বিশেষজ্ঞের কলব্যাক নিশ্চিত করতে ১ চাপুন, অনুরোধ বাতিল করতে ২ চাপুন।',
            'end_text' => 'ধন্যবাদ। কৃষি-বন্ধুর বিশেষজ্ঞ সেবার সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['আপনার বিশেষজ্ঞ কলব্যাক অনুরোধ নিশ্চিত করা হয়েছে। আমাদের কৃষি বিশেষজ্ঞ দ্রুত যোগাযোগ করবেন।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['আপনার বিশেষজ্ঞ কলব্যাক অনুরোধ বাতিল করা হয়েছে।']],
            ],
        ],
        'market_price_alert' => [
            'title' => 'বাজার দর ভয়েস অ্যালার্ট',
            'start_text' => 'হ্যালো {{name}}, আজকের {{district}} এলাকার বাজার দর আপডেট রয়েছে।',
            'question_text' => 'আজকের ফসলের বাজার দর শুনতে ১ চাপুন, ফসল বিক্রয় বিজ্ঞাপন দিতে ২ চাপুন, পরে শুনতে চাইলে ৩ চাপুন।',
            'end_text' => 'ধন্যবাদ। বাজার দর জানতে কৃষি-বন্ধুর সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['আজকের বাজার দর জানতে কৃষি-বন্ধু অ্যাপের বাজার দর বিভাগ দেখুন। নিয়মিত বাজার দর আপডেট পেতে অ্যাপ ব্যবহার করুন।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['ফসল বিক্রয় বিজ্ঞাপন দিতে কৃষি-বন্ধু অ্যাপের ফসল বিক্রয় বিভাগ ব্যবহার করুন।']],
                ['key' => '3', 'option_type' => 'voice', 'texts' => ['ঠিক আছে। আমরা আপনাকে পরে বাজার দর আপডেট জানাবো।']],
            ],
        ],
        'equipment_inquiry' => [
            'title' => 'কৃষি সরঞ্জাম ক্রেতা আগ্রহ',
            'start_text' => 'হ্যালো {{name}}, আপনার {{product}} পণ্যে একজন ক্রেতা আগ্রহ দেখিয়েছেন।',
            'question_text' => 'ক্রেতার তথ্য পেতে ১ চাপুন, আগ্রহ বাতিল করতে ২ চাপুন, পরে যোগাযোগ করতে চাইলে ৩ চাপুন।',
            'end_text' => 'ধন্যবাদ। কৃষি সরঞ্জাম বাজারে কৃষি-বন্ধুর সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['আপনার অনুরোধ গ্রহণ করা হয়েছে। ক্রেতার যোগাযোগ তথ্য অ্যাপ নোটিফিকেশন বা এস এম এস এর মাধ্যমে পাঠানো হবে।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['ঠিক আছে। এই ক্রেতার আগ্রহ বাতিল করা হয়েছে।']],
                ['key' => '3', 'option_type' => 'voice', 'texts' => ['ঠিক আছে। পরে যোগাযোগ করার অনুরোধ সংরক্ষণ করা হয়েছে।']],
            ],
        ],
    ];
}
