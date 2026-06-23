@extends('layouts.app')

@section('title', 'ভয়েস অটোমেশন')

@section('content')
<style>
    .admin-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .admin-table th { text-align: left; padding: .5rem .6rem; background: var(--green-50); color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
    .admin-table td { padding: .5rem .6rem; border-bottom: 1px solid var(--border); vertical-align: top; }
    .st { padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
    .st-sent { background: var(--green-100); color: var(--green-600); }
    .st-failed { background: var(--red-100); color: var(--red-500); }
    .st-simulated { background: var(--amber-100); color: #7a5200; }
    .st-queued { background: var(--sky-100); color: var(--sky-500); }
    .card h3 { font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; }
    .tpl details { border: 1px solid var(--border); border-radius: var(--radius-sm); margin-bottom: .6rem; }
    .tpl summary { padding: .6rem .8rem; cursor: pointer; font-weight: 600; font-size: 14px; color: var(--green-700); }
    .var-hint code { background: var(--green-50); padding: 1px 6px; border-radius: 4px; font-size: 12px; color: var(--green-600); }
    .mini-stat { display: grid; grid-template-columns: repeat(3,1fr); gap: .5rem; margin-bottom: 1rem; }
    .mini-stat div { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: .6rem; text-align: center; }
    .mini-stat .n { font-size: 1.3rem; font-weight: 700; color: var(--green-500); }
    .mini-stat .l { font-size: 11px; color: var(--text-muted); }
</style>

<a href="{{ route('admin.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← অ্যাডমিন</a>
<h2 class="page-title">📞 ভয়েস অটোমেশন (প্রতিধ্বনি)</h2>

<div class="mini-stat">
    <div><div class="n">{{ $stats['queued'] }}</div><div class="l">সারিবদ্ধ</div></div>
    <div><div class="n">{{ $stats['sent'] }}</div><div class="l">পাঠানো</div></div>
    <div><div class="n">{{ $stats['failed'] }}</div><div class="l">ব্যর্থ</div></div>
</div>

{{-- Settings --}}
<div class="card">
    <h3>⚙️ API সেটিংস</h3>
    <form method="POST" action="{{ route('admin.voice.settings') }}">
        @csrf
        <div class="form-group">
            <label>API Base URL</label>
            <input type="url" name="api_base_url" value="{{ $settings->api_base_url }}" placeholder="https://dashboard.protiddhoni-bd.com/api/surveys/direct-tts" required>
        </div>
        <div class="form-group">
            <label>API Token
                @if($settings->api_token)<span class="badge badge-green">সংরক্ষিত আছে</span>@else<span class="badge badge-red">সেট করা নেই</span>@endif
            </label>
            <input type="password" name="api_token" placeholder="{{ $settings->api_token ? 'পরিবর্তন করতে নতুন টোকেন দিন' : 'Bearer Token' }}" autocomplete="off">
        </div>
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>Sender নম্বর</label>
                <input type="text" name="sender" value="{{ $settings->sender }}" placeholder="09612254680" required>
            </div>
            <div class="form-group">
                <label>ভয়েস</label>
                <select name="default_voice">
                    <option value="female" {{ $settings->default_voice === 'female' ? 'selected' : '' }}>মহিলা (female)</option>
                    <option value="male" {{ $settings->default_voice === 'male' ? 'selected' : '' }}>পুরুষ (male)</option>
                </select>
            </div>
        </div>
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>ভাষা কোড</label>
                <input type="text" name="language_code" value="{{ $settings->language_code }}" placeholder="bn" required>
            </div>
            <div class="form-group">
                <label>মডিউল</label>
                <select name="status">
                    <option value="1" {{ $settings->status ? 'selected' : '' }}>সক্রিয়</option>
                    <option value="0" {{ ! $settings->status ? 'selected' : '' }}>নিষ্ক্রিয়</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">সেটিংস সংরক্ষণ করুন</button>
    </form>
    @unless($settings->isEnabled())
        <p style="font-size: 12px; color: #7a5200; margin-top: .75rem;">⚠️ মডিউল সম্পূর্ণ সক্রিয় নয় — Token + Sender দিন ও স্ট্যাটাস "সক্রিয়" করুন। ততক্ষণ কল শুধু সিমুলেট (লগ) হবে।</p>
    @endunless
    <p style="font-size: 12px; color: var(--text-muted); margin-top: .5rem;">DTMF কলব্যাক URL: <code>{{ url('/voice/callback') }}</code></p>
</div>

{{-- Test call --}}
<div class="card">
    <h3>🧪 টেস্ট ভয়েস কল</h3>
    <form method="POST" action="{{ route('admin.voice.test') }}">
        @csrf
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group"><label>মোবাইল</label><input type="tel" name="phone" placeholder="01XXXXXXXXX" maxlength="11" required></div>
            <div class="form-group">
                <label>ফিচার</label>
                <select name="feature" required>
                    @foreach(\App\Models\VoiceTemplate::TYPES as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">কল পাঠান</button>
    </form>
</div>

{{-- District campaign --}}
<div class="card">
    <h3>📢 জেলাভিত্তিক ভয়েস ক্যাম্পেইন</h3>
    <form method="POST" action="{{ route('admin.voice.campaign') }}">
        @csrf
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>ফিচার</label>
                <select name="feature" required>
                    <option value="weather_alert">স্মার্ট আবহাওয়া সতর্কতা</option>
                    <option value="govt_circular">সরকারি বিজ্ঞপ্তি</option>
                    <option value="labor_match">শ্রমিক সেবা ম্যাচিং</option>
                </select>
            </div>
            <div class="form-group">
                <label>জেলা</label>
                <select name="district" required>
                    <option value="">নির্বাচন করুন</option>
                    @foreach($districts as $d)<option value="{{ $d->bn_name }}">{{ $d->bn_name }}</option>@endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-secondary">কল সারিবদ্ধ করুন →</button>
        <p style="font-size: 12px; color: var(--text-muted); margin-top: .5rem;">কলগুলো সারিবদ্ধ হবে; ক্রন (<code>voice:dispatch</code>) ছোট ব্যাচে পাঠাবে।</p>
    </form>
</div>

{{-- Templates --}}
<div class="card tpl">
    <h3>📝 ভয়েস টেমপ্লেট</h3>
    <p class="var-hint" style="font-size: 12px; color: var(--text-muted); margin-bottom: .75rem;">
        ভেরিয়েবল: <code>@{{name}}</code> <code>@{{district}}</code> <code>@{{crop}}</code> <code>@{{product}}</code> <code>@{{service}}</code> <code>@{{date}}</code>
    </p>
    @foreach($templates as $tpl)
        <details>
            <summary>{{ $tpl->title }} <span class="badge {{ $tpl->status ? 'badge-green' : 'badge-red' }}">{{ $tpl->status ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span></summary>
            <form method="POST" action="{{ route('admin.voice.templates.update', $tpl) }}" style="padding: .8rem;">
                @csrf
                <div class="form-group"><label>শিরোনাম</label><input type="text" name="title" value="{{ $tpl->title }}" required></div>
                <div class="form-group"><label>শুরুর বার্তা (start)</label><input type="text" name="start_text" value="{{ $tpl->start_text }}"></div>
                <div class="form-group"><label>মূল প্রশ্ন (question) *</label><textarea name="question_text" required>{{ $tpl->question_text }}</textarea></div>
                <div class="form-group"><label>শেষ বার্তা (end)</label><input type="text" name="end_text" value="{{ $tpl->end_text }}"></div>
                <div class="form-group">
                    <label>DTMF অপশন (JSON)</label>
                    <textarea name="dtmf_options" rows="5" style="font-family: monospace; font-size: 12px;">{{ json_encode($tpl->dtmf_options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                </div>
                <div class="form-group">
                    <label>স্ট্যাটাস</label>
                    <select name="status"><option value="1" {{ $tpl->status ? 'selected' : '' }}>সক্রিয়</option><option value="0" {{ ! $tpl->status ? 'selected' : '' }}>নিষ্ক্রিয়</option></select>
                </div>
                <button type="submit" class="btn btn-primary">টেমপ্লেট সংরক্ষণ</button>
            </form>
        </details>
    @endforeach
</div>

{{-- Pending DTMF callback requests --}}
@if($callbacks->count())
<div class="card">
    <h3>📥 কলব্যাক অনুরোধ ({{ $callbacks->count() }})</h3>
    <table class="admin-table">
        <thead><tr><th>সময়</th><th>ফিচার</th><th>ফোন</th><th>ব্যবহারকারী</th><th></th></tr></thead>
        <tbody>
            @foreach($callbacks as $cb)
                <tr>
                    <td style="white-space: nowrap;">{{ $cb->created_at->format('d/m H:i') }}</td>
                    <td>{{ \App\Models\VoiceTemplate::TYPES[$cb->feature_type] ?? $cb->feature_type }}</td>
                    <td>{{ $cb->phone }}</td>
                    <td>{{ $cb->user->name ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.voice.callbacks.done', $cb) }}">@csrf @method('PATCH')
                            <button class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px;">✅ সম্পন্ন</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Logs --}}
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3>📜 কল লগ</h3>
        <form method="POST" action="{{ route('admin.voice.retry') }}">@csrf
            <button class="btn btn-secondary" style="padding: 5px 12px; font-size: 12px;">🔁 ব্যাচ প্রসেস করুন</button>
        </form>
    </div>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead><tr><th>সময়</th><th>ফোন</th><th>ফিচার</th><th>DTMF</th><th>রিট্রাই</th><th>স্ট্যাটাস</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="white-space: nowrap;">{{ $log->created_at->format('d/m H:i') }}</td>
                        <td>{{ $log->phone }}</td>
                        <td>{{ \App\Models\VoiceTemplate::TYPES[$log->feature_type] ?? $log->feature_type }}</td>
                        <td>{{ $log->dtmf_key ?? '—' }}</td>
                        <td>{{ $log->retry_count }}</td>
                        <td><span class="st st-{{ $log->call_status }}">{{ $log->call_status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 1rem;">কোনো লগ নেই</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: .75rem;">{{ $logs->links() }}</div>
</div>
@endsection
