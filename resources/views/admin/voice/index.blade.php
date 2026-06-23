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

    /* Tabs */
    .vtabs { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .4rem; margin-bottom: 1rem; -webkit-overflow-scrolling: touch; }
    .vtabs button { flex-shrink: 0; padding: 8px 14px; border-radius: 999px; font-size: 13px; font-weight: 600; background: var(--card); border: 1px solid var(--border); color: var(--text-secondary); white-space: nowrap; }
    .vtabs button.active { background: var(--green-500); color: #fff; border-color: var(--green-500); }
    .vtab-panel { display: none; }
    .vtab-panel.active { display: block; animation: fadeIn .2s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }

    .mini-stat { display: grid; grid-template-columns: repeat(3,1fr); gap: .5rem; margin-bottom: 1rem; }
    .mini-stat div { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: .6rem; text-align: center; }
    .mini-stat .n { font-size: 1.3rem; font-weight: 700; color: var(--green-500); }
    .mini-stat .l { font-size: 11px; color: var(--text-muted); }

    .tpl-card { border: 1px solid var(--border); border-radius: var(--radius-sm); margin-bottom: .75rem; overflow: hidden; }
    .tpl-card > summary { padding: .7rem .85rem; cursor: pointer; font-weight: 600; font-size: 14px; color: var(--green-700); display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; list-style: none; }
    .tpl-card > summary::-webkit-details-marker { display: none; }
    .tpl-card > summary::before { content: '▸'; color: var(--text-muted); }
    .tpl-card[open] > summary::before { content: '▾'; }
    .tpl-body { padding: .85rem; border-top: 1px solid var(--border); background: var(--green-50); }
    .tpl-actions { display: flex; gap: .4rem; flex-wrap: wrap; margin-top: .5rem; }
    .var-hint code { background: var(--green-50); padding: 1px 6px; border-radius: 4px; font-size: 12px; color: var(--green-600); }
    .json-area { font-family: monospace; font-size: 12px; }
    .muted-note { font-size: 12px; color: var(--text-muted); margin-top: .5rem; }
</style>

<a href="{{ route('admin.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← অ্যাডমিন</a>
<h2 class="page-title">📞 ভয়েস অটোমেশন (প্রতিধ্বনি)</h2>

<div class="mini-stat">
    <div><div class="n">{{ $stats['queued'] }}</div><div class="l">সারিবদ্ধ</div></div>
    <div><div class="n">{{ $stats['sent'] }}</div><div class="l">পাঠানো</div></div>
    <div><div class="n">{{ $stats['failed'] }}</div><div class="l">ব্যর্থ</div></div>
</div>

@unless($settings->isEnabled())
    <div class="alert" style="background: var(--amber-50); border: 1px solid var(--amber-100); color: #7a5200;">
        ⚠️ মডিউল সম্পূর্ণ সক্রিয় নয় — “API সেটিংস” ট্যাবে Token + Sender দিন ও স্ট্যাটাস “সক্রিয়” করুন। ততক্ষণ কল শুধু সিমুলেট (লগ) হবে।
    </div>
@endunless

{{-- Tab bar --}}
<div class="vtabs">
    <button type="button" data-tab="settings">⚙️ API সেটিংস</button>
    <button type="button" data-tab="test">🧪 টেস্ট কল</button>
    <button type="button" data-tab="campaign">📢 ক্যাম্পেইন</button>
    <button type="button" data-tab="templates">📝 টেমপ্লেট</button>
    <button type="button" data-tab="logs">📜 কল লগ</button>
</div>

{{-- ============== TAB 1: API SETTINGS ============== --}}
<section class="vtab-panel" data-panel="settings">
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
                <div class="muted-note">🔒 টোকেন এনক্রিপ্ট করে সংরক্ষণ হয়; কখনও স্ক্রিনে বা পেলোডে দেখানো হয় না।</div>
            </div>
            <div class="grid-2" style="gap: .5rem;">
                <div class="form-group">
                    <label>Sender নম্বর</label>
                    <input type="text" name="sender" value="{{ $settings->sender }}" placeholder="09612254680" required>
                </div>
                <div class="form-group">
                    <label>ডিফল্ট ভয়েস</label>
                    <select name="default_voice">
                        <option value="female" {{ $settings->default_voice === 'female' ? 'selected' : '' }}>মহিলা (female)</option>
                        <option value="male" {{ $settings->default_voice === 'male' ? 'selected' : '' }}>পুরুষ (male)</option>
                    </select>
                </div>
            </div>
            <div class="grid-2" style="gap: .5rem;">
                <div class="form-group">
                    <label>ডিফল্ট ভাষা কোড</label>
                    <input type="text" name="language_code" value="{{ $settings->language_code }}" placeholder="bn" required>
                </div>
                <div class="form-group">
                    <label>মডিউল স্ট্যাটাস</label>
                    <select name="status">
                        <option value="1" {{ $settings->status ? 'selected' : '' }}>সক্রিয়</option>
                        <option value="0" {{ ! $settings->status ? 'selected' : '' }}>নিষ্ক্রিয়</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">💾 সেটিংস সংরক্ষণ করুন</button>
        </form>
        <div class="muted-note">DTMF কলব্যাক URL (প্রতিধ্বনি ড্যাশবোর্ডে দিন): <code>{{ url('/voice/callback') }}</code></div>
    </div>
</section>

{{-- ============== TAB 2: TEST CALL ============== --}}
<section class="vtab-panel" data-panel="test">
    <div class="card">
        <h3>🧪 টেস্ট ভয়েস কল</h3>
        <form method="POST" action="{{ route('admin.voice.test') }}">
            @csrf
            <div class="grid-2" style="gap: .5rem;">
                <div class="form-group"><label>মোবাইল নম্বর</label><input type="tel" name="phone" placeholder="01XXXXXXXXX" maxlength="11" required></div>
                <div class="form-group">
                    <label>টেমপ্লেট / ফিচার</label>
                    <select name="feature" required>
                        @foreach(\App\Models\VoiceTemplate::TYPES as $val => $lbl)
                            <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">📞 টেস্ট কল পাঠান</button>
        </form>
        <div class="muted-note">নির্বাচিত ফিচারের <strong>সক্রিয় টেমপ্লেট</strong> ব্যবহার করে কল করা হবে।</div>
    </div>
</section>

{{-- ============== TAB 3: CAMPAIGN ============== --}}
<section class="vtab-panel" data-panel="campaign">
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
            <button type="submit" class="btn btn-primary">📤 কল সারিবদ্ধ করুন</button>
        </form>
        <div class="muted-note">কলগুলো সারিবদ্ধ হবে; ক্রন (<code>voice:dispatch</code>) প্রতি মিনিটে ছোট ব্যাচে পাঠাবে — শেয়ার্ড হোস্টিং-উপযোগী।</div>
    </div>
</section>

{{-- ============== TAB 4: TEMPLATE MANAGEMENT ============== --}}
<section class="vtab-panel" data-panel="templates">
    <div class="card">
        <h3>📝 টেমপ্লেট ব্যবস্থাপনা</h3>
        <p class="var-hint" style="font-size: 12px; color: var(--text-muted); margin-bottom: .75rem;">
            ডায়নামিক ভেরিয়েবল: <code>@{{name}}</code> <code>@{{district}}</code> <code>@{{crop}}</code> <code>@{{product}}</code> <code>@{{service}}</code> <code>@{{date}}</code>
        </p>

        {{-- Create new template --}}
        <details class="tpl-card">
            <summary style="color: var(--green-600);">➕ নতুন টেমপ্লেট তৈরি করুন</summary>
            <div class="tpl-body">
                <form method="POST" action="{{ route('admin.voice.templates.store') }}">
                    @csrf
                    @include('admin.voice._template_fields', ['t' => null])
                    <button type="submit" class="btn btn-primary">➕ টেমপ্লেট তৈরি করুন</button>
                </form>
            </div>
        </details>

        {{-- Existing templates --}}
        @forelse($templates as $tpl)
            <details class="tpl-card">
                <summary>
                    {{ $tpl->title }}
                    <span class="badge {{ $tpl->status ? 'badge-green' : 'badge-red' }}">{{ $tpl->status ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
                    <span class="badge badge-sky" style="font-weight: 500;">{{ \App\Models\VoiceTemplate::TYPES[$tpl->type] ?? $tpl->type }}</span>
                </summary>
                <div class="tpl-body">
                    <form method="POST" action="{{ route('admin.voice.templates.update', $tpl) }}">
                        @csrf
                        @include('admin.voice._template_fields', ['t' => $tpl])
                        <button type="submit" class="btn btn-primary">💾 সংরক্ষণ</button>
                    </form>
                    <div class="tpl-actions">
                        <form method="POST" action="{{ route('admin.voice.templates.toggle', $tpl) }}">@csrf @method('PATCH')
                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;">{{ $tpl->status ? '🚫 নিষ্ক্রিয় করুন' : '✅ সক্রিয় করুন' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.voice.templates.destroy', $tpl) }}" onsubmit="return confirm('এই টেমপ্লেট মুছে ফেলবেন?')">@csrf @method('DELETE')
                            <button class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;">🗑️ মুছুন</button>
                        </form>
                    </div>
                </div>
            </details>
        @empty
            <p style="text-align: center; color: var(--text-muted); padding: 1rem;">কোনো টেমপ্লেট নেই — উপরে নতুন তৈরি করুন।</p>
        @endforelse
    </div>
</section>

{{-- ============== TAB 5: CALL LOGS ============== --}}
<section class="vtab-panel" data-panel="logs">
    @if($callbacks->count())
    <div class="card">
        <h3>📥 কলব্যাক অনুরোধ ({{ $callbacks->count() }})</h3>
        <div style="overflow-x: auto;">
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
    </div>
    @endif

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3>📜 কল লগ</h3>
            <form method="POST" action="{{ route('admin.voice.retry') }}">@csrf
                <button class="btn btn-secondary" style="padding: 5px 12px; font-size: 12px;">🔁 ব্যাচ প্রসেস</button>
            </form>
        </div>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead><tr><th>সময়</th><th>ফোন</th><th>ফিচার</th><th>DTMF</th><th>রিট্রাই</th><th>স্ট্যাটাস</th><th></th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td style="white-space: nowrap;">{{ $log->created_at->format('d/m H:i') }}</td>
                            <td>{{ $log->phone }}</td>
                            <td>{{ \App\Models\VoiceTemplate::TYPES[$log->feature_type] ?? $log->feature_type }}</td>
                            <td>{{ $log->dtmf_key ?? '—' }}</td>
                            <td>{{ $log->retry_count }}</td>
                            <td><span class="st st-{{ $log->call_status }}">{{ $log->call_status }}</span></td>
                            <td>
                                @if(in_array($log->call_status, ['failed', 'queued']))
                                    <form method="POST" action="{{ route('admin.voice.logs.retry', $log) }}">@csrf
                                        <button class="btn btn-secondary" style="padding: 3px 9px; font-size: 12px;">🔁 রিট্রাই</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 1rem;">কোনো লগ নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: .75rem;">{{ $logs->links() }}</div>
    </div>
</section>

@push('scripts')
<script>
    (function () {
        var tabs = document.querySelectorAll('.vtabs button');
        var panels = document.querySelectorAll('.vtab-panel');
        var valid = Array.prototype.map.call(tabs, function (t) { return t.dataset.tab; });

        function activate(name) {
            if (valid.indexOf(name) === -1) name = valid[0];
            tabs.forEach(function (t) { t.classList.toggle('active', t.dataset.tab === name); });
            panels.forEach(function (p) { p.classList.toggle('active', p.dataset.panel === name); });
            try { localStorage.setItem('kb_voice_tab', name); } catch (e) {}
        }
        tabs.forEach(function (t) { t.addEventListener('click', function () { activate(t.dataset.tab); }); });

        var initial = (location.hash || '').replace('#vt-', '');
        if (!initial) { try { initial = localStorage.getItem('kb_voice_tab'); } catch (e) {} }
        activate(initial || valid[0]);
    })();
</script>
@endpush
@endsection
