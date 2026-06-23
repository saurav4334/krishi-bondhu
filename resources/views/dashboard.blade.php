@extends('layouts.app')

@section('title', 'ড্যাশবোর্ড')

@section('content')
<style>
    .weather-card { background: linear-gradient(135deg, var(--sky-400), var(--sky-500)); color: #fff; border-radius: var(--radius-lg); padding: 1.25rem; margin-bottom: 1rem; }
    .weather-card h3 { font-family: 'Hind Siliguri', sans-serif; font-weight: 600; opacity: .9; font-size: 14px; }
    .weather-card .temp { font-size: 2.5rem; font-weight: 700; }
    .weather-card .detail { opacity: .85; font-size: 13px; margin-top: .25rem; }
    .quick-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: .65rem; margin-bottom: 1rem; }
    .quick-btn { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: .75rem .5rem; display: flex; flex-direction: column; align-items: center; gap: 5px; color: var(--text-secondary); font-size: 12px; font-weight: 500; cursor: pointer; transition: all .2s; text-align: center; text-decoration: none; }
    .quick-btn:hover { background: var(--green-50); border-color: var(--green-200); color: var(--green-600); transform: translateY(-2px); }
    .quick-btn .icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
    .stat-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: .65rem; margin-bottom: 1rem; }
    .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: .85rem; text-align: center; }
    .stat-card .num { font-size: 1.5rem; font-weight: 700; color: var(--green-500); }
    .stat-card .lbl { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .tip-card { background: var(--green-50); border: 1px solid var(--green-100); border-radius: var(--radius); padding: 1rem; margin-bottom: .65rem; display: flex; gap: .75rem; align-items: flex-start; }
    .tip-card .tip-icon { font-size: 1.5rem; flex-shrink: 0; margin-top: 2px; }
    .tip-card h4 { font-size: 14px; color: var(--green-700); font-family: 'Hind Siliguri', sans-serif; font-weight: 600; }
    .tip-card p { font-size: 13px; color: var(--text-secondary); margin-top: 2px; }
    .scan-hist-item { display: flex; gap: .75rem; align-items: center; padding: .75rem 0; border-bottom: 1px solid var(--border); }
    .scan-hist-item:last-child { border: none; }
    .scan-thumb { width: 48px; height: 48px; border-radius: 8px; background: var(--green-50); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; overflow: hidden; }
    .scan-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .scan-hist-item .info h4 { font-size: 14px; font-weight: 600; font-family: 'Hind Siliguri', sans-serif; }
    .scan-hist-item .info p { font-size: 12px; color: var(--text-muted); }
</style>

<a href="{{ route('weather.index') }}" class="weather-card" style="display: block; color: #fff;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h3>{{ $weather['emoji'] }} আজকের আবহাওয়া</h3>
            <div class="temp">{{ $weather['temp'] }}°C</div>
            <div class="detail">{{ $weather['desc'] }} · আর্দ্রতা {{ $weather['humidity'] }}%</div>
            <div class="detail">📍 {{ $weather['district'] ?: 'বাংলাদেশ' }}</div>
        </div>
        <div style="text-align: right; opacity: .85;">
            <div style="font-size: 2rem;">{{ $weather['emoji'] }}</div>
            <div style="font-size: 12px; margin-top: .3rem;">বাতাস {{ $weather['wind'] }} কি.মি./ঘ.</div>
        </div>
    </div>
</a>

@foreach($weatherAlerts as $alert)
    <a href="{{ route('weather.index') }}" style="display: flex; gap: .6rem; align-items: center; padding: .7rem .9rem; background: var(--red-50); border: 1px solid var(--red-100); border-radius: var(--radius); margin-bottom: .6rem; color: var(--red-500);">
        <span style="font-size: 1.4rem;">{{ $alert->icon }}</span>
        <div><strong style="font-size: 14px; font-family: 'Hind Siliguri', sans-serif;">{{ $alert->title }}</strong>
        <div style="font-size: 11px; opacity: .85;">{{ $alert->type_label }} · {{ $alert->alert_date->format('d/m/Y') }}</div></div>
    </a>
@endforeach

<div class="quick-actions">
    <a href="{{ route('scan.index') }}" class="quick-btn">
        <div class="icon" style="background: var(--green-50);">🔬</div>
        রোগ স্ক্যান
    </a>
    <a href="{{ route('prices.index') }}" class="quick-btn">
        <div class="icon" style="background: var(--amber-50);">💰</div>
        বাজার দর
    </a>
    <a href="{{ route('market.index') }}" class="quick-btn">
        <div class="icon" style="background: var(--brown-50);">🛒</div>
        ফসল বিক্রয়
    </a>
    <a href="{{ route('equipment.index') }}" class="quick-btn">
        <div class="icon" style="background: var(--amber-50);">🚜</div>
        কৃষি সরঞ্জাম
    </a>
    <a href="{{ route('experts.index') }}" class="quick-btn">
        <div class="icon" style="background: var(--sky-50);">👨‍🔬</div>
        বিশেষজ্ঞ
    </a>
    <a href="{{ route('labor.index') }}" class="quick-btn">
        <div class="icon" style="background: var(--amber-50);">👷</div>
        শ্রমিক খুঁজুন
    </a>
    <a href="{{ route('transport.index') }}" class="quick-btn">
        <div class="icon" style="background: var(--sky-50);">🚜</div>
        পরিবহন বুক করুন
    </a>
    <a href="{{ route('news.index') }}" class="quick-btn">
        <div class="icon" style="background: var(--green-50);">📰</div>
        কৃষি সংবাদ
    </a>
    <a href="{{ route('weather.index') }}" class="quick-btn">
        <div class="icon" style="background: var(--sky-50);">🌦️</div>
        আবহাওয়া
    </a>
</div>

<div class="stat-cards">
    <div class="stat-card"><div class="num">{{ $stats['scans'] }}</div><div class="lbl">মোট স্ক্যান</div></div>
    <div class="stat-card"><div class="num">{{ $stats['posts'] }}</div><div class="lbl">আমার পোস্ট</div></div>
    <div class="stat-card"><div class="num">{{ $stats['prices'] }}</div><div class="lbl">বাজারের পণ্য</div></div>
</div>

<div class="section-title">💡 কৃষি পরামর্শ</div>
@foreach($tips as $tip)
    <div class="tip-card">
        <div class="tip-icon">{{ $tip['icon'] }}</div>
        <div>
            <h4>{{ $tip['title'] }}</h4>
            <p>{{ $tip['body'] }}</p>
        </div>
    </div>
@endforeach

@if($latestNews->count())
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div class="section-title">📰 সর্বশেষ সংবাদ</div>
        <a href="{{ route('news.index') }}" style="font-size: 12px; color: var(--green-500); font-weight: 600;">সব দেখুন →</a>
    </div>
    <div class="card card-sm">
        @foreach($latestNews as $news)
            <a href="{{ route('news.show', $news) }}" class="scan-hist-item" style="color: inherit;">
                <div class="scan-thumb">
                    @if($news->image)<img src="{{ asset('storage/' . $news->image) }}" alt="">@else 📰 @endif
                </div>
                <div class="info" style="flex: 1;">
                    <h4>{{ $news->title }}</h4>
                    <p>{{ $news->category->name ?? '' }} · {{ optional($news->published_at)->diffForHumans() ?? $news->created_at->diffForHumans() }}</p>
                </div>
                @if($news->is_important)<span class="badge badge-red">★</span>@endif
            </a>
        @endforeach
    </div>
@endif

@if($recent_scans->count())
    <div class="section-title">📊 সাম্প্রতিক স্ক্যান</div>
    <div class="card card-sm">
        @foreach($recent_scans as $scan)
            <div class="scan-hist-item">
                <div class="scan-thumb">
                    @if($scan->image)
                        <img src="{{ asset('storage/' . $scan->image) }}" alt="">
                    @else
                        🌿
                    @endif
                </div>
                <div class="info" style="flex: 1;">
                    <h4>{{ $scan->disease_name }}</h4>
                    <p>নির্ভরযোগ্যতা: {{ $scan->confidence_score }}% · {{ $scan->created_at->diffForHumans() }}</p>
                </div>
                <span class="badge badge-green">সম্পন্ন</span>
            </div>
        @endforeach
    </div>
@endif
@endsection
