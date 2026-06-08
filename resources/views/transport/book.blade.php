@extends('layouts.app')

@section('title', 'পরিবহন বুকিং')

@section('content')
<h2 class="page-title">📦 পরিবহন বুকিং</h2>

@if($provider)
    <div class="card card-sm" style="display: flex; align-items: center; gap: .75rem; background: var(--sky-50); border-color: var(--sky-100);">
        <div style="font-size: 1.8rem;">🚛</div>
        <div>
            <h4 style="font-size: 14px; font-family: 'Hind Siliguri', sans-serif; font-weight: 600;">{{ $provider->driver_name }}</h4>
            <p style="font-size: 12px; color: var(--text-muted);">{{ $provider->vehicle_type }}@if($provider->rate_per_km) · ৳{{ number_format($provider->rate_per_km, 0) }}/কি.মি.@endif</p>
        </div>
    </div>
@endif

<div class="card">
    <form method="POST" action="{{ route('transport.booking.store') }}">
        @csrf
        @if($provider)
            <input type="hidden" name="transport_provider_id" value="{{ $provider->id }}">
        @endif

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>পিকআপ লোকেশন *</label>
                <input type="text" name="pickup_location" value="{{ old('pickup_location') }}" placeholder="যেখান থেকে" required>
            </div>
            <div class="form-group">
                <label>ডেলিভারি লোকেশন *</label>
                <input type="text" name="delivery_location" value="{{ old('delivery_location') }}" placeholder="যেখানে" required>
            </div>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>ফসলের ধরন</label>
                <input type="text" name="crop_type" value="{{ old('crop_type') }}" placeholder="যেমন: ধান">
            </div>
            <div class="form-group">
                <label>পরিমাণ / ওজন</label>
                <input type="text" name="quantity" value="{{ old('quantity') }}" placeholder="যেমন: ৫০ মণ">
            </div>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>পছন্দের তারিখ</label>
                <input type="date" name="booking_date" value="{{ old('booking_date') }}" min="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>যোগাযোগ নম্বর *</label>
                <input type="tel" name="contact_number" value="{{ old('contact_number', auth()->user()->mobile) }}" placeholder="01XXXXXXXXX" maxlength="11" required>
            </div>
        </div>

        <div style="display: flex; gap: .5rem;">
            <a href="{{ route('transport.index') }}" class="btn btn-secondary" style="flex: 1; justify-content: center;">বাতিল</a>
            <button type="submit" class="btn btn-primary" style="flex: 2; justify-content: center;">বুকিং নিশ্চিত করুন</button>
        </div>
    </form>
</div>
@endsection
