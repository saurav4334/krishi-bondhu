<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\User;
use App\Models\WeatherAlert;
use App\Services\ProtiddhoniVoiceService;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WeatherAlertController extends Controller
{
    public function __construct(protected SmsService $sms, protected ProtiddhoniVoiceService $voice)
    {
    }

    public function index(): View
    {
        return view('admin.weather.index', [
            'alerts' => WeatherAlert::orderByDesc('alert_date')->paginate(20),
            'districts' => District::active()->orderBy('bn_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        WeatherAlert::create($this->validateData($request));

        return back()->with('success', 'আবহাওয়া সতর্কতা যুক্ত হয়েছে।');
    }

    public function destroy(WeatherAlert $alert): RedirectResponse
    {
        $alert->delete();

        return back()->with('success', 'সতর্কতা মুছে ফেলা হয়েছে।');
    }

    /** Manually SMS this alert to farmers in its district. */
    public function sendSms(WeatherAlert $alert): RedirectResponse
    {
        $message = "আবহাওয়া সতর্কতা ({$alert->district}): {$alert->title}। {$alert->description}";
        $sent = 0;

        User::where('role', 'farmer')->where('district', $alert->district)
            ->select('mobile')->chunk(100, function ($chunk) use ($message, &$sent) {
                $mobiles = $chunk->pluck('mobile')->all();
                $this->sms->send($mobiles, $message, 'weather');
                $sent += count($mobiles);
            });

        return back()->with('success', $sent
            ? "{$alert->district} জেলার {$sent} জন কৃষককে SMS পাঠানো হয়েছে।"
            : "{$alert->district} জেলায় কোনো কৃষক পাওয়া যায়নি।");
    }

    /** Queue an automated Bengali voice alert to farmers in this alert's district. */
    public function sendVoice(WeatherAlert $alert): RedirectResponse
    {
        $queued = $this->voice->dispatchToFarmersInDistrict(
            'weather_alert',
            $alert->district,
            ['date' => $alert->alert_date->format('d/m/Y')],
            $alert->id
        );

        return back()->with('success', $queued
            ? "{$alert->district} জেলার {$queued} জন কৃষকের জন্য ভয়েস কল সারিবদ্ধ হয়েছে।"
            : "{$alert->district} জেলায় কোনো কৃষক পাওয়া যায়নি।");
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'district' => ['required', 'string', 'max:50'],
            'alert_type' => ['required', Rule::in(array_keys(WeatherAlert::TYPES))],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'severity' => ['required', Rule::in(array_keys(WeatherAlert::SEVERITIES))],
            'alert_date' => ['required', 'date'],
        ], [
            'district.required' => 'জেলা নির্বাচন করুন',
            'title.required' => 'শিরোনাম দিন',
            'description.required' => 'বিবরণ দিন',
            'alert_date.required' => 'তারিখ দিন',
        ]);
    }
}
