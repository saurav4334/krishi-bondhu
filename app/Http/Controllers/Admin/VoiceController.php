<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\VoiceCallbackRequest;
use App\Models\VoiceCallLog;
use App\Models\VoiceSetting;
use App\Models\VoiceTemplate;
use App\Services\ProtiddhoniVoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VoiceController extends Controller
{
    public function __construct(protected ProtiddhoniVoiceService $voice)
    {
    }

    public function index(): View
    {
        return view('admin.voice.index', [
            'settings' => VoiceSetting::current(),
            'templates' => VoiceTemplate::orderBy('id')->get(),
            'districts' => District::active()->orderBy('bn_name')->get(),
            'logs' => VoiceCallLog::with('user:id,name')->latest()->paginate(15),
            'callbacks' => VoiceCallbackRequest::with('user:id,name')->where('status', 'pending')->latest()->get(),
            'stats' => [
                'queued' => VoiceCallLog::where('call_status', 'queued')->count(),
                'sent' => VoiceCallLog::where('call_status', 'sent')->count(),
                'failed' => VoiceCallLog::where('call_status', 'failed')->count(),
            ],
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'api_base_url' => ['required', 'url', 'max:255'],
            'api_token' => ['nullable', 'string', 'max:1000'],
            'sender' => ['required', 'string', 'max:50'],
            'default_voice' => ['required', 'in:male,female'],
            'language_code' => ['required', 'string', 'max:8'],
            'status' => ['nullable', 'boolean'],
        ], [
            'api_base_url.required' => 'API URL দিন',
            'sender.required' => 'Sender নম্বর দিন',
        ]);

        $settings = VoiceSetting::current();
        $settings->api_base_url = $validated['api_base_url'];
        $settings->sender = $validated['sender'];
        $settings->default_voice = $validated['default_voice'];
        $settings->language_code = $validated['language_code'];
        $settings->status = $request->boolean('status');
        // Only overwrite the token when a new one is typed (field is never pre-filled)
        if (! empty($validated['api_token'])) {
            $settings->api_token = $validated['api_token'];
        }
        $settings->save();

        return back()->with('success', 'ভয়েস সেটিংস সংরক্ষিত হয়েছে।');
    }

    public function updateTemplate(Request $request, VoiceTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'start_text' => ['nullable', 'string', 'max:500'],
            'question_text' => ['required', 'string', 'max:1000'],
            'end_text' => ['nullable', 'string', 'max:500'],
            'dtmf_options' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
        ], [
            'question_text.required' => 'প্রশ্ন/মূল বার্তা দিন',
        ]);

        $dtmf = $template->dtmf_options;
        if (! empty($validated['dtmf_options'])) {
            $decoded = json_decode($validated['dtmf_options'], true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return back()->with('error', 'DTMF অপশন সঠিক JSON নয়।')->withInput();
            }
            $dtmf = $decoded;
        }

        $template->update([
            'title' => $validated['title'],
            'start_text' => $validated['start_text'] ?? null,
            'question_text' => $validated['question_text'],
            'end_text' => $validated['end_text'] ?? null,
            'dtmf_options' => $dtmf,
            'status' => $request->boolean('status'),
        ]);

        return back()->with('success', 'টেমপ্লেট আপডেট হয়েছে।');
    }

    public function sendTest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'feature' => ['required', Rule::in(array_keys(VoiceTemplate::TYPES))],
        ], ['phone.regex' => 'সঠিক মোবাইল নম্বর দিন']);

        $log = $this->voice->sendTestCall($validated['phone'], $validated['feature']);

        return back()->with('success', $log->call_status === 'simulated'
            ? 'টেস্ট কল সিমুলেট করা হয়েছে (মডিউল নিষ্ক্রিয়/অসম্পূর্ণ) — লগ দেখুন।'
            : ('টেস্ট কল স্ট্যাটাস: ' . $log->call_status));
    }

    /** Queue a district-wide voice campaign (weather / govt circular). */
    public function campaign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'feature' => ['required', 'in:weather_alert,govt_circular,labor_match'],
            'district' => ['required', 'string', 'max:50'],
        ], ['district.required' => 'জেলা নির্বাচন করুন']);

        $queued = $this->voice->dispatchToFarmersInDistrict(
            $validated['feature'],
            $validated['district'],
            ['date' => now()->format('d/m/Y')]
        );

        return back()->with('success', $queued
            ? "{$validated['district']} জেলার {$queued} জন কৃষকের জন্য ভয়েস কল সারিবদ্ধ হয়েছে। ক্রন প্রতি মিনিটে ব্যাচে পাঠাবে।"
            : "{$validated['district']} জেলায় কোনো কৃষক পাওয়া যায়নি।");
    }

    /** Flush a batch now (manual trigger; cron does this automatically). */
    public function retry(): RedirectResponse
    {
        $sent = $this->voice->processBatch();

        return back()->with('success', "{$sent} টি সারিবদ্ধ/ব্যর্থ কল প্রক্রিয়া করা হয়েছে।");
    }

    public function markCallbackDone(VoiceCallbackRequest $callback): RedirectResponse
    {
        $callback->update(['status' => 'done']);

        return back()->with('success', 'কলব্যাক অনুরোধ সম্পন্ন হিসেবে চিহ্নিত হয়েছে।');
    }
}
