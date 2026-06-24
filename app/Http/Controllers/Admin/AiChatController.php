<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChatLog;
use App\Models\AiSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiChatController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('q');

        $logs = AiChatLog::with('user:id,name')
            ->when($search, fn ($query) => $query->where('question', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.ai.index', [
            'settings' => AiSetting::current(),
            'logs' => $logs,
            'search' => $search,
            'topics' => $this->topTopics(),
            'stats' => [
                'total' => AiChatLog::count(),
                'today' => AiChatLog::whereDate('created_at', today())->count(),
                'failed' => AiChatLog::where('status', 'failed')->count(),
            ],
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'boolean'],
            'daily_limit' => ['required', 'integer', 'min:1', 'max:200'],
            'guest_limit' => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            'daily_limit.required' => 'দৈনিক সীমা দিন',
            'guest_limit.required' => 'গেস্ট সীমা দিন',
        ]);

        AiSetting::current()->update([
            'status' => $request->boolean('status'),
            'daily_limit' => $validated['daily_limit'],
            'guest_limit' => $validated['guest_limit'],
        ]);

        return back()->with('success', 'AI সেটিংস সংরক্ষিত হয়েছে।');
    }

    /** Simple "most asked topics": frequency of known agri keywords in questions. */
    protected function topTopics(): array
    {
        $keywords = [
            'ধান' => 'ধান', 'গম' => 'গম', 'ভুট্টা' => 'ভুট্টা', 'টমেটো' => 'টমেটো', 'আলু' => 'আলু',
            'সবজি' => 'সবজি', 'সার' => 'সার', 'কীটনাশক' => 'কীটনাশক', 'পোকা' => 'পোকা', 'রোগ' => 'রোগ',
            'সেচ' => 'সেচ', 'বীজ' => 'বীজ', 'ট্রাক্টর' => 'ট্রাক্টর', 'বাজার' => 'বাজার দর', 'বৃষ্টি' => 'আবহাওয়া',
        ];

        $counts = [];
        foreach ($keywords as $kw => $label) {
            $c = AiChatLog::where('question', 'like', "%{$kw}%")->count();
            if ($c > 0) {
                $counts[$label] = ($counts[$label] ?? 0) + $c;
            }
        }
        arsort($counts);

        return array_slice($counts, 0, 8, true);
    }
}
