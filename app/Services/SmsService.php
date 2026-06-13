<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\SmsLog;
use App\Models\SmsSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $sendUrl = 'https://portal.notifybd.com/api/v1/sms/send';
    protected string $balanceUrl = 'https://portal.notifybd.com/api/v1/balance';

    public const OTP_TTL_MINUTES = 5;
    public const OTP_MAX_PER_HOUR = 3;

    /**
     * Send an SMS to one or more numbers via NotifyBD. Falls back to a logged
     * "simulated" send when the module isn't fully configured/enabled, so OTP
     * and dev flows keep working without a live gateway.
     *
     * @return array{status:string, simulated:bool, response:?string}
     */
    public function send(string|array $contacts, string $message, string $purpose = 'general', ?int $userId = null): array
    {
        $settings = SmsSetting::current();
        $list = is_array($contacts) ? array_values(array_filter($contacts)) : [$contacts];
        $joined = implode(',', $list);

        $apiKey = $settings->api_key ?: config('services.notify_bd.api_key');
        $sender = $settings->sender_id ?: config('services.notify_bd.sender_id');
        $live = $settings->status && $apiKey && $sender;

        if (! $live) {
            return $this->record($userId, $joined, $message, $purpose, 'simulated',
                'SMS module disabled or not configured — message logged only.', count($list));
        }

        try {
            $response = Http::asForm()->timeout(15)->post($this->sendUrl, [
                'api_key' => $apiKey,
                'type' => $settings->sms_type,        // unicode for Bengali
                'contacts' => $joined,
                'senderid' => $sender,
                'msg' => $message,
                'label' => $settings->label,          // transactional / promotional
            ]);

            $ok = $response->successful();
            return $this->record($userId, $joined, $message, $purpose, $ok ? 'sent' : 'failed',
                $response->body(), count($list));
        } catch (\Throwable $e) {
            Log::error('NotifyBD send failed: ' . $e->getMessage());
            return $this->record($userId, $joined, $message, $purpose, 'failed', $e->getMessage(), count($list));
        }
    }

    /** Query the gateway SMS balance (best-effort; returns null if unavailable). */
    public function balance(): ?string
    {
        $settings = SmsSetting::current();
        $apiKey = $settings->api_key ?: config('services.notify_bd.api_key');
        if (! $apiKey) {
            return null;
        }

        try {
            $res = Http::timeout(12)->get($this->balanceUrl, ['api_key' => $apiKey]);
            if (! $res->successful()) {
                return null;
            }
            $data = $res->json();
            // Gateways vary; surface the most likely balance field, else raw body.
            return (string) ($data['balance'] ?? $data['data']['balance'] ?? $res->body());
        } catch (\Throwable $e) {
            Log::error('NotifyBD balance failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate + send an OTP. Enforces 5-min expiry and max 3 requests / hour.
     *
     * @return array{ok:bool, error:?string, simulated:bool, otp:?string}
     */
    public function generateOtp(string $mobile, string $purpose): array
    {
        $recent = OtpCode::where('mobile', $mobile)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recent >= self::OTP_MAX_PER_HOUR) {
            return ['ok' => false, 'error' => 'প্রতি ঘণ্টায় সর্বোচ্চ ৩ বার OTP চাওয়া যায়। কিছুক্ষণ পর আবার চেষ্টা করুন।', 'simulated' => false, 'otp' => null];
        }

        $otp = (string) random_int(100000, 999999);

        OtpCode::create([
            'mobile' => $mobile,
            'otp' => $otp,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ]);

        $message = "কৃষি-বন্ধু OTP কোড: {$otp} (৫ মিনিটের জন্য বৈধ)। কাউকে এই কোড দেবেন না।";
        $result = $this->send($mobile, $message, 'otp_' . $purpose);

        return [
            'ok' => $result['status'] !== 'failed',
            'error' => null,
            'simulated' => $result['simulated'],
            'otp' => $result['simulated'] ? $otp : null, // surfaced only in simulate mode
        ];
    }

    /** Verify an OTP for a mobile + purpose; marks it used on success. */
    public function verifyOtp(string $mobile, string $otp, string $purpose): bool
    {
        $row = OtpCode::where('mobile', $mobile)
            ->where('purpose', $purpose)
            ->where('otp', $otp)
            ->valid()
            ->latest('id')
            ->first();

        if (! $row) {
            return false;
        }

        $row->update(['verified_at' => now()]);
        return true;
    }

    protected function record(?int $userId, string $mobile, string $message, string $purpose, string $status, ?string $response, int $recipients): array
    {
        SmsLog::create([
            'user_id' => $userId,
            'mobile' => mb_strlen($mobile) > 250 ? mb_substr($mobile, 0, 247) . '...' : $mobile,
            'message' => $message,
            'purpose' => $purpose,
            'response' => $response,
            'status' => $status,
            'recipients' => $recipients,
        ]);

        return ['status' => $status, 'simulated' => $status === 'simulated', 'response' => $response];
    }
}
