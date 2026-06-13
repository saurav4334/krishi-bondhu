<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\WeatherAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WeatherAlertController extends Controller
{
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
