<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\WeatherAlert;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeatherController extends Controller
{
    public function __construct(protected WeatherService $weather)
    {
    }

    public function index(Request $request): View
    {
        $districts = District::active()->orderBy('bn_name')->get();
        $district = $request->input('district', Auth::user()->district) ?: 'ঢাকা';

        $weather = $this->weather->getWeather($district);

        $alerts = WeatherAlert::activeFor($district)->orderBy('alert_date')->get();

        return view('weather.index', compact('weather', 'alerts', 'district', 'districts'));
    }
}
