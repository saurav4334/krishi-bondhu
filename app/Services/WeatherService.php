<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /** Bengali district (bn_name) -> OpenWeather "City,Country" query. */
    protected array $owmQuery = [
        'ঢাকা' => 'Dhaka,BD', 'চট্টগ্রাম' => 'Chattogram,BD', 'রাজশাহী' => 'Rajshahi,BD',
        'খুলনা' => 'Khulna,BD', 'বরিশাল' => 'Barisal,BD', 'সিলেট' => 'Sylhet,BD',
        'রংপুর' => 'Rangpur,BD', 'ময়মনসিংহ' => 'Mymensingh,BD', 'বগুড়া' => 'Bogra,BD',
        'যশোর' => 'Jessore,BD', 'দিনাজপুর' => 'Dinajpur,BD', 'কুমিল্লা' => 'Comilla,BD',
        'নড়াইল' => 'Narail,BD',
    ];

    /**
     * Current weather for a district. Uses OpenWeather when a key is set
     * (file-cached per district), otherwise a deterministic mock so the
     * dashboard always renders.
     */
    public function getWeather(?string $district): array
    {
        $district = trim((string) $district) ?: 'ঢাকা';
        $ttl = now()->addMinutes((int) env('WEATHER_CACHE_TTL', 60));

        return Cache::remember("weather:{$district}", $ttl, function () use ($district) {
            return $this->fetchLive($district) ?? $this->mock($district);
        });
    }

    protected function fetchLive(string $district): ?array
    {
        $key = config('services.openweather.key');
        if (empty($key)) {
            return null;
        }

        $query = $this->owmQuery[$district] ?? "{$district},BD";

        try {
            $res = Http::timeout(8)->retry(1, 200)->get('https://api.openweathermap.org/data/2.5/weather', [
                'q' => $query, 'appid' => $key, 'units' => 'metric', 'lang' => 'bn',
            ]);

            if (! $res->successful()) {
                Log::warning("OpenWeather {$district}: HTTP {$res->status()}");
                return null;
            }

            $d = $res->json();

            return [
                'district' => $district,
                'temp' => (int) round($d['main']['temp'] ?? 0),
                'desc' => $d['weather'][0]['description'] ?? 'আবহাওয়া',
                'humidity' => (int) ($d['main']['humidity'] ?? 0),
                'wind' => (int) round(($d['wind']['speed'] ?? 0) * 3.6),
                'emoji' => $this->emoji($d['weather'][0]['id'] ?? 800),
                'source' => 'live',
            ];
        } catch (\Throwable $e) {
            Log::error('OpenWeather exception: ' . $e->getMessage());
            return null;
        }
    }

    protected function emoji(int $code): string
    {
        return match (true) {
            $code >= 200 && $code < 300 => '⛈️',
            $code >= 300 && $code < 600 => '🌧️',
            $code >= 600 && $code < 700 => '🌨️',
            $code >= 700 && $code < 800 => '🌫️',
            $code === 800 => '☀️',
            default => '🌤️',
        };
    }

    protected function mock(string $district): array
    {
        $sets = [
            ['temp' => 31, 'desc' => 'আংশিক মেঘলা', 'humidity' => 78, 'wind' => 12, 'emoji' => '🌤️'],
            ['temp' => 33, 'desc' => 'রৌদ্রোজ্জ্বল', 'humidity' => 65, 'wind' => 8, 'emoji' => '☀️'],
            ['temp' => 28, 'desc' => 'হালকা বৃষ্টি', 'humidity' => 88, 'wind' => 15, 'emoji' => '🌧️'],
            ['temp' => 30, 'desc' => 'মেঘলা আকাশ', 'humidity' => 74, 'wind' => 10, 'emoji' => '☁️'],
        ];
        srand(crc32($district));
        $s = $sets[rand(0, count($sets) - 1)];
        srand();

        return $s + ['district' => $district, 'source' => 'mock'];
    }
}
