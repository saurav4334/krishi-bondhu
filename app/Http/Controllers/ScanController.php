<?php

namespace App\Http\Controllers;

use App\Models\DiseaseScan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ScanController extends Controller
{
    public function index(): View
    {
        $scans = DiseaseScan::where('user_id', Auth::id())
            ->latest()
            ->take(10)
            ->get();

        return view('scan.index', compact('scans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:10240'],
        ], [
            'image.required' => 'ছবি আপলোড করুন',
            'image.image' => 'শুধুমাত্র ছবি আপলোড করুন',
            'image.mimes' => 'JPG, PNG, WebP ফরম্যাট সমর্থিত',
            'image.max' => 'ছবির আকার সর্বোচ্চ ১০MB হতে হবে',
        ]);

        $user = Auth::user();

        // Daily scan limit check
        if ($user->last_scan_date && $user->last_scan_date->isToday()) {
            if ($user->daily_scan_count >= 10) {
                return back()->withErrors(['image' => 'দৈনিক স্ক্যান সীমা (১০) অতিক্রম হয়েছে']);
            }
        } else {
            $user->daily_scan_count = 0;
        }

        // Save uploaded image
        $path = $request->file('image')->store('scans', 'public');

        // Call AI service (Gemini) - using mock for now
        $result = $this->analyzeWithAI(storage_path('app/public/' . $path));

        // Save to database
        $scan = DiseaseScan::create([
            'user_id' => $user->id,
            'image' => $path,
            'disease_name' => $result['disease'],
            'confidence_score' => $result['confidence'],
            'symptoms' => $result['symptoms'],
            'treatment' => $result['treatment'],
            'prevention' => $result['prevention'],
            'ai_result' => $result,
            'status' => 'completed',
        ]);

        // Update scan count
        $user->update([
            'daily_scan_count' => $user->daily_scan_count + 1,
            'last_scan_date' => now()->toDateString(),
        ]);

        return back()->with([
            'scan_result' => $scan,
            'success' => 'বিশ্লেষণ সম্পন্ন হয়েছে',
        ]);
    }

    /**
     * AI analysis. Replace this with Gemini Vision API call in production.
     */
    protected function analyzeWithAI(string $imagePath): array
    {
        // PRODUCTION: Uncomment to use Gemini Vision API
        // $apiKey = config('services.gemini.api_key');
        // $imageData = base64_encode(file_get_contents($imagePath));
        // $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
        //     'contents' => [[
        //         'parts' => [
        //             ['text' => 'এই ফসলের ছবি বিশ্লেষণ করে রোগ শনাক্ত করুন। বাংলায় উত্তর দিন...'],
        //             ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $imageData]]
        //         ]
        //     ]]
        // ]);
        // return $this->parseGeminiResponse($response->json());

        // Mock AI response (development mode)
        $diseases = [
            [
                'disease' => 'ধানের ব্লাস্ট রোগ',
                'symptoms' => 'পাতায় হীরা আকৃতির বাদামি দাগ, কেন্দ্রে ধূসর রঙ দেখা যায়।',
                'treatment' => 'ট্রাইসাইক্লাজল ০.১% দ্রবণ স্প্রে করুন। ৭-১০ দিন পরপর ৩ বার।',
                'prevention' => 'রোগ প্রতিরোধী জাত ব্যবহার করুন। অতিরিক্ত নাইট্রোজেন সার এড়িয়ে চলুন।',
            ],
            [
                'disease' => 'আলুর লেট ব্লাইট',
                'symptoms' => 'পাতায় বাদামি-কালো দাগ, নিচে সাদা ছত্রাক দেখা যায়।',
                'treatment' => 'ম্যানকোজেব ২ গ্রাম/লিটার পানিতে মিশিয়ে স্প্রে করুন।',
                'prevention' => 'রোগমুক্ত বীজ ব্যবহার করুন। সঠিক দূরত্বে রোপণ করুন।',
            ],
            [
                'disease' => 'টমেটোর মোজাইক রোগ',
                'symptoms' => 'পাতায় হলুদ-সবুজ মোজাইক নকশা, পাতা কুঁকড়ে যায়।',
                'treatment' => 'আক্রান্ত গাছ সরিয়ে ফেলুন। ইমিডাক্লোপ্রিড স্প্রে করুন।',
                'prevention' => 'রোগমুক্ত চারা ব্যবহার করুন। সাদা মাছি দমন করুন।',
            ],
            [
                'disease' => 'গমের রাস্ট রোগ',
                'symptoms' => 'পাতা ও কাণ্ডে কমলা-বাদামি পাউডার জমে।',
                'treatment' => 'প্রোপিকোনাজল ০.১% স্প্রে করুন। আক্রান্ত গাছ পুড়িয়ে দিন।',
                'prevention' => 'প্রতিরোধী জাত চাষ করুন। সময়মতো রোপণ করুন।',
            ],
            [
                'disease' => 'বেগুনের ঢলে পড়া রোগ',
                'symptoms' => 'গাছ হঠাৎ ঢলে পড়ে, শিকড় পচে যায়।',
                'treatment' => 'কপার অক্সিক্লোরাইড ৩ গ্রাম/লিটার মাটিতে প্রয়োগ করুন।',
                'prevention' => 'জলাবদ্ধতা এড়িয়ে চলুন। উঁচু বেডে চাষ করুন।',
            ],
        ];

        $selected = $diseases[array_rand($diseases)];
        $selected['confidence'] = rand(75, 95);
        return $selected;
    }
}
