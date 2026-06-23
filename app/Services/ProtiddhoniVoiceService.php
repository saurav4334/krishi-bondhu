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
        'weather_alert' => ['2'],       // expert callback requested
        'crop_lead' => ['1'],           // send buyer/contact info
        'equipment_rental' => ['1', '2'], // booking confirmed / rejected
        'labor_match' => ['1'],         // interested
        'govt_circular' => ['1'],       // details requested
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
        return $this->dispatch('crop_lead', $phone, $vars, $userId, $relatedId, $immediate);
    }

    public function sendEquipmentRentalConfirmation(string $phone, array $vars = [], ?int $userId = null, $relatedId = null, bool $immediate = false): VoiceCallLog
    {
        return $this->dispatch('equipment_rental', $phone, $vars, $userId, $relatedId, $immediate);
    }

    public function sendLaborMatchCall(string $phone, array $vars = [], ?int $userId = null, $relatedId = null, bool $immediate = false): VoiceCallLog
    {
        return $this->dispatch('labor_match', $phone, $vars, $userId, $relatedId, $immediate);
    }

    public function sendGovernmentCircularAlert(string $phone, array $vars = [], ?int $userId = null, $relatedId = null, bool $immediate = false): VoiceCallLog
    {
        return $this->dispatch('govt_circular', $phone, $vars, $userId, $relatedId, $immediate);
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
            'voice' => $settings->default_voice ?: 'female',
            'language_code' => $settings->language_code ?: 'bn',
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
            'start_text' => 'হ্যালো {{name}}।',
            'question_text' => 'আপনার এলাকা {{district}}-এ আগামী ২৪ ঘন্টার মধ্যে ভারী বৃষ্টির সম্ভাবনা রয়েছে। ফসল নিরাপদ রাখতে ১ চাপুন, কৃষি বিশেষজ্ঞের সাথে কথা বলতে ২ চাপুন।',
            'end_text' => 'ধন্যবাদ। কৃষি-বন্ধুর সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['নিরাপত্তা পরামর্শ গ্রহণ করা হয়েছে। ধন্যবাদ।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['একজন কৃষি বিশেষজ্ঞ শীঘ্রই আপনাকে কল করবেন।']],
            ],
        ],
        'crop_lead' => [
            'start_text' => 'হ্যালো {{name}}।',
            'question_text' => 'আপনার {{crop}} বিজ্ঞাপনে একজন ক্রেতা আগ্রহ দেখিয়েছেন। ক্রেতার তথ্য পেতে ১ চাপুন, আগ্রহী না হলে ২ চাপুন।',
            'end_text' => 'ধন্যবাদ। কৃষি-বন্ধুর সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['ক্রেতার যোগাযোগ তথ্য আপনাকে এসএমএস করা হবে।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['ঠিক আছে, ধন্যবাদ।']],
            ],
        ],
        'equipment_rental' => [
            'start_text' => 'হ্যালো {{name}}।',
            'question_text' => 'আপনার {{product}} ভাড়ার একটি বুকিং অনুরোধ এসেছে। বুকিং কনফার্ম করতে ১ চাপুন, বাতিল করতে ২ চাপুন।',
            'end_text' => 'ধন্যবাদ। কৃষি-বন্ধুর সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['বুকিং নিশ্চিত করা হয়েছে।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['বুকিং বাতিল করা হয়েছে।']],
            ],
        ],
        'labor_match' => [
            'start_text' => 'হ্যালো {{name}}।',
            'question_text' => 'আপনার এলাকা {{district}}-এ কৃষি শ্রমিকের নতুন একটি কাজ পাওয়া গেছে। আগ্রহী হলে ১ চাপুন, না হলে ২ চাপুন।',
            'end_text' => 'ধন্যবাদ। কৃষি-বন্ধুর সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['আপনার আগ্রহ গ্রহণ করা হয়েছে। শীঘ্রই বিস্তারিত জানানো হবে।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['ঠিক আছে, ধন্যবাদ।']],
            ],
        ],
        'govt_circular' => [
            'start_text' => 'হ্যালো {{name}}।',
            'question_text' => 'কৃষি বিষয়ক একটি সরকারি বিজ্ঞপ্তি প্রকাশিত হয়েছে। বিস্তারিত শুনতে ১ চাপুন, পরে শুনতে ২ চাপুন।',
            'end_text' => 'ধন্যবাদ। কৃষি-বন্ধুর সাথে থাকুন।',
            'dtmf_options' => [
                ['key' => '1', 'option_type' => 'voice', 'texts' => ['বিস্তারিত তথ্য আপনাকে জানানো হবে।']],
                ['key' => '2', 'option_type' => 'voice', 'texts' => ['ঠিক আছে, পরে আবার জানানো হবে।']],
            ],
        ],
    ];
}
