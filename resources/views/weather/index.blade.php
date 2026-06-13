@extends('layouts.app')

@section('title', 'আবহাওয়া')

@section('content')
<style>
    .wx-hero { background: linear-gradient(135deg, var(--sky-400), var(--sky-500)); color: #fff; border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; }
    .wx-hero .temp { font-size: 3rem; font-weight: 700; line-height: 1; }
    .wx-hero .desc { font-size: 15px; opacity: .95; margin-top: .35rem; }
    .wx-hero .detail { font-size: 13px; opacity: .85; margin-top: .15rem; }
    .wx-hero .ico { font-size: 3.5rem; }
    .wx-select { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); background: #fff; font-size: 15px; margin-bottom: 1rem; }
    .alert-card { display: flex; gap: .75rem; padding: 1rem; border-radius: var(--radius); margin-bottom: .75rem; border: 1px solid; }
    .alert-card .ico { font-size: 1.8rem; flex-shrink: 0; }
    .alert-card h4 { font-size: 15px; font-family: 'Hind Siliguri', sans-serif; font-weight: 600; }
    .alert-card p { font-size: 13px; margin-top: .25rem; opacity: .9; }
    .alert-card .when { font-size: 11px; margin-top: .4rem; opacity: .8; }
    .sev-low { background: var(--green-50); border-color: var(--green-200); color: var(--green-700); }
    .sev-moderate { background: var(--amber-50); border-color: var(--amber-100); color: #7a5200; }
    .sev-high { background: #fff3e0; border-color: #ffcc80; color: #b45309; }
    .sev-severe { background: var(--red-50); border-color: var(--red-100); color: var(--red-500); }
</style>

<h2 class="page-title">🌦️ স্মার্ট আবহাওয়া</h2>

<form method="GET">
    <select name="district" class="wx-select" onchange="this.form.submit()">
        @foreach($districts as $d)
            <option value="{{ $d->bn_name }}" {{ $district === $d->bn_name ? 'selected' : '' }}>{{ $d->bn_name }}</option>
        @endforeach
    </select>
</form>

<div class="wx-hero">
    <div>
        <div style="font-size: 14px; opacity: .9;">📍 {{ $weather['district'] }}</div>
        <div class="temp">{{ $weather['temp'] }}°C</div>
        <div class="desc">{{ $weather['desc'] }}</div>
        <div class="detail">💧 আর্দ্রতা {{ $weather['humidity'] }}% · 💨 বাতাস {{ $weather['wind'] }} কি.মি./ঘ.</div>
        @if(($weather['source'] ?? '') === 'mock')<div class="detail">(ডেমো ডেটা)</div>@endif
    </div>
    <div class="ico">{{ $weather['emoji'] }}</div>
</div>

<div class="section-title">⚠️ {{ $district }} জেলার সতর্কবার্তা</div>
@forelse($alerts as $alert)
    <div class="alert-card sev-{{ $alert->severity }}">
        <div class="ico">{{ $alert->icon }}</div>
        <div>
            <h4>{{ $alert->title }} <span class="badge" style="background: rgba(0,0,0,.06);">{{ $alert->severity_label }}</span></h4>
            <p>{{ $alert->description }}</p>
            <div class="when">{{ $alert->type_label }} · 📅 {{ $alert->alert_date->translatedFormat('d M Y') }}</div>
        </div>
    </div>
@empty
    <div class="card" style="text-align: center; color: var(--text-muted);">
        ✅ এই মুহূর্তে আপনার জেলায় কোনো সক্রিয় সতর্কতা নেই।
    </div>
@endforelse
@endsection
