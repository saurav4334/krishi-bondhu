@extends('layouts.app')

@section('title', 'AI চ্যাট ব্যবস্থাপনা')

@section('content')
<style>
    .admin-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .admin-table th { text-align: left; padding: .5rem .6rem; background: var(--green-50); color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
    .admin-table td { padding: .5rem .6rem; border-bottom: 1px solid var(--border); vertical-align: top; }
    .st { padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
    .st-success { background: var(--green-100); color: var(--green-600); }
    .st-failed { background: var(--red-100); color: var(--red-500); }
    .st-simulated { background: var(--amber-100); color: #7a5200; }
    .card h3 { font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; }
    .mini-stat { display: grid; grid-template-columns: repeat(3,1fr); gap: .5rem; margin-bottom: 1rem; }
    .mini-stat div { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: .6rem; text-align: center; }
    .mini-stat .n { font-size: 1.3rem; font-weight: 700; color: var(--green-500); }
    .mini-stat .l { font-size: 11px; color: var(--text-muted); }
    .topic-chip { display: inline-block; background: var(--green-50); color: var(--green-600); border: 1px solid var(--green-100); border-radius: 999px; padding: 4px 11px; font-size: 12px; margin: 0 4px 6px 0; }
</style>

<a href="{{ route('admin.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← অ্যাডমিন</a>
<h2 class="page-title">🤖 কৃষি AI সহকারী</h2>

<div class="mini-stat">
    <div><div class="n">{{ $stats['total'] }}</div><div class="l">মোট প্রশ্ন</div></div>
    <div><div class="n">{{ $stats['today'] }}</div><div class="l">আজকের প্রশ্ন</div></div>
    <div><div class="n">{{ $stats['failed'] }}</div><div class="l">ব্যর্থ</div></div>
</div>

{{-- Settings --}}
<div class="card">
    <h3>⚙️ সেটিংস</h3>
    <form method="POST" action="{{ route('admin.ai.settings') }}">
        @csrf
        <div class="grid-3" style="gap: .5rem;">
            <div class="form-group">
                <label>AI চ্যাট</label>
                <select name="status">
                    <option value="1" {{ $settings->status ? 'selected' : '' }}>সক্রিয়</option>
                    <option value="0" {{ ! $settings->status ? 'selected' : '' }}>নিষ্ক্রিয়</option>
                </select>
            </div>
            <div class="form-group">
                <label>দৈনিক সীমা (ইউজার)</label>
                <input type="number" name="daily_limit" value="{{ $settings->daily_limit }}" min="1" max="200" required>
            </div>
            <div class="form-group">
                <label>দৈনিক সীমা (গেস্ট)</label>
                <input type="number" name="guest_limit" value="{{ $settings->guest_limit }}" min="0" max="100" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">সেটিংস সংরক্ষণ করুন</button>
    </form>
    @if(empty(config('services.gemini.api_key')))
        <p style="font-size: 12px; color: #7a5200; margin-top: .75rem;">⚠️ Gemini API Key সেট করা নেই (<code>GEMINI_API_KEY</code>) — উত্তর ডেমো মোডে দেওয়া হবে। লাইভ উত্তরের জন্য সার্ভারে কী যোগ করুন।</p>
    @endif
</div>

{{-- Top topics --}}
@if(count($topics))
<div class="card">
    <h3>🔥 সর্বাধিক জিজ্ঞাসিত বিষয়</h3>
    @foreach($topics as $label => $count)
        <span class="topic-chip">{{ $label }} <strong>{{ $count }}</strong></span>
    @endforeach
</div>
@endif

{{-- Chat history + search --}}
<div class="card">
    <h3>📜 চ্যাট ইতিহাস</h3>
    <form method="GET" action="{{ route('admin.ai.index') }}" style="display: flex; gap: .5rem; margin-bottom: .75rem;">
        <input type="text" name="q" value="{{ $search }}" placeholder="🔍 প্রশ্ন খুঁজুন..." style="flex: 1; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
        <button type="submit" class="btn btn-primary">খুঁজুন</button>
        @if($search)<a href="{{ route('admin.ai.index') }}" class="btn btn-secondary">✕</a>@endif
    </form>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead><tr><th>সময়</th><th>ব্যবহারকারী</th><th>প্রশ্ন</th><th>উত্তর</th><th>স্ট্যাটাস</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="white-space: nowrap;">{{ $log->created_at->format('d/m H:i') }}</td>
                        <td>{{ $log->user->name ?? 'গেস্ট' }}</td>
                        <td style="max-width: 220px;">{{ $log->question }}</td>
                        <td style="max-width: 320px; color: var(--text-secondary);">{{ \Illuminate\Support\Str::limit($log->answer, 160) }}</td>
                        <td><span class="st st-{{ $log->status }}">{{ $log->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 1rem;">কোনো চ্যাট লগ নেই</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: .75rem;">{{ $logs->links() }}</div>
</div>
@endsection
