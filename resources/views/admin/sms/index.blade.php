@extends('layouts.app')

@section('title', 'SMS ব্যবস্থাপনা')

@section('content')
<style>
    .admin-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .admin-table th { text-align: left; padding: .5rem .6rem; background: var(--green-50); color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
    .admin-table td { padding: .5rem .6rem; border-bottom: 1px solid var(--border); vertical-align: top; }
    .st { padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
    .st-sent { background: var(--green-100); color: var(--green-600); }
    .st-failed { background: var(--red-100); color: var(--red-500); }
    .st-simulated { background: var(--amber-100); color: #7a5200; }
    .card h3 { font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; }
</style>

<a href="{{ route('admin.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← অ্যাডমিন</a>
<h2 class="page-title">📲 SMS ব্যবস্থাপনা (NotifyBD)</h2>

{{-- Settings --}}
<div class="card">
    <h3>⚙️ গেটওয়ে সেটিংস</h3>
    <form method="POST" action="{{ route('admin.sms.settings') }}">
        @csrf
        <div class="form-group">
            <label>API Key
                @if($settings->api_key)<span class="badge badge-green">সংরক্ষিত আছে</span>@else<span class="badge badge-red">সেট করা নেই</span>@endif
            </label>
            <input type="password" name="api_key" placeholder="{{ $settings->api_key ? 'পরিবর্তন করতে নতুন কী দিন' : 'NotifyBD API Key' }}" autocomplete="off">
        </div>
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>Sender ID</label>
                <input type="text" name="sender_id" value="{{ $settings->sender_id }}" placeholder="অনুমোদিত sender ID">
            </div>
            <div class="form-group">
                <label>টাইপ</label>
                <select name="sms_type">
                    <option value="unicode" {{ $settings->sms_type === 'unicode' ? 'selected' : '' }}>Unicode (বাংলা)</option>
                    <option value="text" {{ $settings->sms_type === 'text' ? 'selected' : '' }}>Text (English)</option>
                </select>
            </div>
        </div>
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>Label</label>
                <select name="label">
                    <option value="transactional" {{ $settings->label === 'transactional' ? 'selected' : '' }}>Transactional</option>
                    <option value="promotional" {{ $settings->label === 'promotional' ? 'selected' : '' }}>Promotional</option>
                </select>
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

    <div style="margin-top: 1rem; display: flex; align-items: center; gap: .75rem;">
        <a href="{{ route('admin.sms.index', ['check_balance' => 1]) }}" class="btn btn-secondary">💰 ব্যালেন্স চেক করুন</a>
        @if($balanceChecked)
            <span style="font-size: 14px;">ব্যালেন্স: <strong>{{ $balance ?? 'পাওয়া যায়নি' }}</strong></span>
        @endif
    </div>
    @unless($settings->isEnabled())
        <p style="font-size: 12px; color: #7a5200; margin-top: .75rem;">⚠️ মডিউল সম্পূর্ণ সক্রিয় নয় — API Key + Sender ID দিন ও স্ট্যাটাস "সক্রিয়" করুন। ততক্ষণ SMS শুধু সিমুলেট (লগ) হবে।</p>
    @endunless
</div>

{{-- Test SMS --}}
<div class="card">
    <h3>✉️ টেস্ট SMS</h3>
    <form method="POST" action="{{ route('admin.sms.test') }}">
        @csrf
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group"><label>মোবাইল</label><input type="tel" name="mobile" placeholder="01XXXXXXXXX" maxlength="11" required></div>
            <div class="form-group"><label>বার্তা</label><input type="text" name="message" placeholder="টেস্ট বার্তা" required></div>
        </div>
        <button type="submit" class="btn btn-primary">পাঠান</button>
    </form>
</div>

{{-- Broadcast --}}
<div class="card">
    <h3>📢 ব্রডকাস্ট SMS</h3>
    @if(session('sms_preview'))
        @php $pv = session('sms_preview'); @endphp
        <div class="alert" style="background: var(--amber-50); border: 1px solid var(--amber-100); color: #7a5200;">
            <strong>{{ $pv['count'] }} জন</strong> প্রাপক নির্বাচিত হয়েছে। নিশ্চিত হলে পাঠান:
            <form method="POST" action="{{ route('admin.sms.broadcast') }}" style="margin-top: .6rem;">
                @csrf
                <input type="hidden" name="role" value="{{ $pv['role'] }}">
                <input type="hidden" name="district" value="{{ $pv['district'] }}">
                <input type="hidden" name="message" value="{{ $pv['message'] }}">
                <input type="hidden" name="confirmed" value="1">
                <button type="submit" class="btn btn-primary" {{ $pv['count'] === 0 ? 'disabled' : '' }}>✅ নিশ্চিত করে {{ $pv['count'] }} জনকে পাঠান</button>
            </form>
        </div>
    @endif
    <form method="POST" action="{{ route('admin.sms.broadcast') }}">
        @csrf
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>ভূমিকা</label>
                <select name="role" required>
                    <option value="all">সকল ব্যবহারকারী</option>
                    <option value="farmer">কৃষক</option>
                    <option value="expert">বিশেষজ্ঞ</option>
                    <option value="admin">অ্যাডমিন</option>
                </select>
            </div>
            <div class="form-group">
                <label>জেলা</label>
                <select name="district">
                    <option value="all">সকল জেলা</option>
                    @foreach($districts as $d)<option value="{{ $d->bn_name }}">{{ $d->bn_name }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="form-group"><label>বার্তা</label><textarea name="message" required placeholder="ব্রডকাস্ট বার্তা..."></textarea></div>
        <button type="submit" class="btn btn-secondary">প্রাপক গণনা করুন →</button>
    </form>
</div>

{{-- Logs --}}
<div class="card">
    <h3>📜 SMS লগ</h3>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead><tr><th>সময়</th><th>প্রাপক</th><th>উদ্দেশ্য</th><th>স্ট্যাটাস</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="white-space: nowrap;">{{ $log->created_at->format('d/m H:i') }}</td>
                        <td>{{ $log->mobile }}@if($log->recipients > 1) <span class="badge badge-sky">{{ $log->recipients }}</span>@endif</td>
                        <td>{{ $log->purpose }}</td>
                        <td><span class="st st-{{ $log->status }}">{{ $log->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1rem;">কোনো লগ নেই</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: .75rem;">{{ $logs->links() }}</div>
</div>
@endsection
