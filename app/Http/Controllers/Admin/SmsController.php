<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\SmsLog;
use App\Models\SmsSetting;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsController extends Controller
{
    public function __construct(protected SmsService $sms)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.sms.index', [
            'settings' => SmsSetting::current(),
            'districts' => District::active()->orderBy('bn_name')->get(),
            'logs' => SmsLog::with('user:id,name')->latest()->paginate(15),
            // Balance is only fetched when explicitly requested (avoids a slow API call per page load)
            'balance' => $request->boolean('check_balance') ? $this->sms->balance() : null,
            'balanceChecked' => $request->boolean('check_balance'),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'api_key' => ['nullable', 'string', 'max:255'],
            'sender_id' => ['nullable', 'string', 'max:50'],
            'sms_type' => ['required', 'in:text,unicode'],
            'label' => ['required', 'in:transactional,promotional'],
            'status' => ['nullable', 'boolean'],
        ]);

        $settings = SmsSetting::current();
        $settings->sender_id = $validated['sender_id'] ?? null;
        $settings->sms_type = $validated['sms_type'];
        $settings->label = $validated['label'];
        $settings->status = $request->boolean('status');
        // Only overwrite the key when a new one is typed (the field is never pre-filled)
        if (! empty($validated['api_key'])) {
            $settings->api_key = $validated['api_key'];
        }
        $settings->save();

        return back()->with('success', 'SMS সেটিংস সংরক্ষিত হয়েছে।');
    }

    public function sendTest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'message' => ['required', 'string', 'max:500'],
        ], ['mobile.regex' => 'সঠিক মোবাইল নম্বর দিন', 'message.required' => 'বার্তা দিন']);

        $res = $this->sms->send($validated['mobile'], $validated['message'], 'test');

        return back()->with('success', $res['simulated']
            ? 'টেস্ট SMS সিমুলেট করা হয়েছে (গেটওয়ে নিষ্ক্রিয়/অসম্পূর্ণ) — লগ দেখুন।'
            : ('টেস্ট SMS স্ট্যাটাস: ' . $res['status']));
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:all,farmer,expert,admin'],
            'district' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:500'],
        ], ['message.required' => 'বার্তা দিন']);

        $query = User::query()
            ->when($validated['role'] !== 'all', fn ($q) => $q->where('role', $validated['role']))
            ->when($validated['district'] && $validated['district'] !== 'all', fn ($q) => $q->where('district', $validated['district']));

        $count = $query->count();

        // Step 1: show recipient count for confirmation before spending SMS.
        if (! $request->boolean('confirmed')) {
            return back()->with('sms_preview', [
                'count' => $count,
                'role' => $validated['role'],
                'district' => $validated['district'],
                'message' => $validated['message'],
            ]);
        }

        if ($count === 0) {
            return back()->with('error', 'নির্বাচিত শর্তে কোনো প্রাপক নেই।');
        }

        // Step 2: send in chunks of 100 numbers per gateway call.
        $sent = 0;
        $query->select('mobile')->chunk(100, function ($chunk) use ($validated, &$sent) {
            $mobiles = $chunk->pluck('mobile')->all();
            $this->sms->send($mobiles, $validated['message'], 'broadcast');
            $sent += count($mobiles);
        });

        return back()->with('success', "{$sent} জন ব্যবহারকারীকে SMS পাঠানো হয়েছে।");
    }
}
