@extends('layouts.app')

@section('title', 'কৃষি পরিবহন সেবা')

@section('content')
<style>
    .prov-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1rem; margin-bottom: .85rem; box-shadow: var(--shadow); display: flex; gap: .85rem; align-items: flex-start; }
    .prov-icon { width: 52px; height: 52px; border-radius: 12px; background: var(--sky-50); display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; }
    .prov-info { flex: 1; min-width: 0; }
    .prov-info h4 { font-size: 15px; font-family: 'Hind Siliguri', sans-serif; font-weight: 600; }
    .prov-info .meta { display: flex; flex-wrap: wrap; gap: .35rem; margin: .4rem 0; }
    .prov-actions { display: flex; gap: .5rem; margin-top: .5rem; }
    .prov-actions a { flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 7px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 500; }
    .btn-call { background: var(--green-50); color: var(--green-600); border: 1px solid var(--green-200); }
    .btn-call:hover { background: var(--green-100); }
    .btn-book { background: var(--sky-50); color: var(--sky-500); border: 1px solid var(--sky-100); }
    .booking-item { padding: .85rem; border: 1px solid var(--border); border-radius: var(--radius-sm); margin-bottom: .6rem; }
    .filter-bar { display: flex; gap: .5rem; margin-bottom: 1rem; }
    .filter-bar input, .filter-bar select { flex: 1; padding: 8px 10px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; }
</style>

@php
    $vehicleEmoji = ['পিকআপ ভ্যান' => '🚐', 'মিনি ট্রাক' => '🚚', 'ট্রাক' => '🚛', 'কভার্ড ভ্যান' => '🚐', 'ট্রাক্টর' => '🚜', 'কোল্ড স্টোরেজ পরিবহন' => '❄️'];
@endphp

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; gap: .5rem;">
    <h2 class="page-title" style="margin: 0; border: none; padding: 0;">🚜 পরিবহন সেবা</h2>
    <div style="display: flex; gap: .4rem;">
        <a href="{{ route('transport.book') }}" class="btn btn-secondary" style="padding: 8px 12px; font-size: 13px;">📦 বুক করুন</a>
        <a href="{{ route('transport.register') }}" class="btn btn-primary" style="padding: 8px 12px; font-size: 13px;">+ গাড়ি</a>
    </div>
</div>

<form method="GET" action="{{ route('transport.index') }}" class="filter-bar">
    <input type="text" name="district" value="{{ $filters['district'] ?? '' }}" placeholder="জেলা খুঁজুন">
    <select name="vehicle_type" onchange="this.form.submit()">
        <option value="">সব গাড়ি</option>
        @foreach($vehicles as $v)
            <option value="{{ $v }}" @selected(($filters['vehicle_type'] ?? '') === $v)>{{ $v }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary" style="padding: 8px 12px;">🔍</button>
</form>

@if($bookings->count())
    <div class="section-title">📦 আমার বুকিং</div>
    @foreach($bookings as $booking)
        @php
            $statusBadge = ['pending' => 'badge-amber', 'confirmed' => 'badge-sky', 'completed' => 'badge-green', 'cancelled' => 'badge-red'][$booking->status] ?? 'badge-amber';
            $statusLabel = ['pending' => 'অপেক্ষমাণ', 'confirmed' => 'নিশ্চিত', 'completed' => 'সম্পন্ন', 'cancelled' => 'বাতিল'][$booking->status] ?? $booking->status;
        @endphp
        <div class="booking-item">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h4 style="font-size: 14px; font-family: 'Hind Siliguri', sans-serif; font-weight: 600;">{{ $booking->pickup_location }} → {{ $booking->delivery_location }}</h4>
                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
            </div>
            <div class="meta" style="display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .4rem;">
                @if($booking->crop_type)<span class="badge badge-green">🌾 {{ $booking->crop_type }}</span>@endif
                @if($booking->quantity)<span class="badge badge-amber">⚖️ {{ $booking->quantity }}</span>@endif
                @if($booking->booking_date)<span class="badge badge-sky">📅 {{ $booking->booking_date->format('d-m-Y') }}</span>@endif
                @if($booking->provider)<span class="badge badge-green">🚛 {{ $booking->provider->driver_name }}</span>@endif
            </div>
        </div>
    @endforeach
@endif

<div class="section-title">🚛 পরিবহন তালিকা</div>
@forelse($providers as $provider)
    <div class="prov-card">
        <div class="prov-icon">{{ $vehicleEmoji[$provider->vehicle_type] ?? '🚚' }}</div>
        <div class="prov-info">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: .5rem;">
                <h4>{{ $provider->driver_name }}</h4>
                @if($provider->availability_status === 'available')
                    <span class="badge badge-green">✅ মুক্ত</span>
                @else
                    <span class="badge badge-red">⛔ ব্যস্ত</span>
                @endif
            </div>
            <div class="meta">
                <span class="badge badge-sky">🚛 {{ $provider->vehicle_type }}</span>
                @if($provider->rate_per_km)<span class="badge badge-green">৳{{ number_format($provider->rate_per_km, 0) }}/কি.মি.</span>@endif
            </div>
            <div class="meta">
                <span class="badge badge-amber">📍 {{ $provider->district }}</span>
                @if($provider->service_area)<span class="badge badge-sky">🗺️ {{ $provider->service_area }}</span>@endif
                @if($provider->vehicle_number)<span class="badge badge-amber">🔢 {{ $provider->vehicle_number }}</span>@endif
            </div>
            <div class="prov-actions">
                <a href="tel:{{ $provider->mobile }}" class="btn-call">📞 কল করুন</a>
                <a href="{{ route('transport.book', $provider) }}" class="btn-book">📦 বুক করুন</a>
            </div>
        </div>
    </div>
@empty
    <div class="card" style="text-align: center; color: var(--text-muted);">
        <p style="font-size: 2rem;">🚜</p>
        <p>এখনো কোনো পরিবহন নেই</p>
        <a href="{{ route('transport.register') }}" class="btn btn-primary" style="margin-top: .75rem;">গাড়ি রেজিস্টার করুন</a>
    </div>
@endforelse

{{ $providers->links() }}
@endsection
