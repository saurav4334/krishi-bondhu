@extends('layouts.app')

@section('title', 'পাসওয়ার্ড রিসেট')

@section('content')
<style>
    .auth-page { max-width: 440px; margin: 0 auto; padding: 2rem 1rem; }
    .auth-hero { text-align: center; padding: 1.5rem 0 1rem; }
    .auth-link { text-align: center; margin-top: 1rem; color: var(--text-secondary); font-size: 14px; }
    .auth-link a { color: var(--green-600); font-weight: 500; }
</style>

<div class="auth-page">
    <div class="auth-hero">
        <img src="{{ asset('images/logo.png') }}" alt="কৃষি-বন্ধু" style="width: min(60%, 180px); height: auto;">
        <h2 style="color: var(--green-600); font-family: 'Noto Serif Bengali', serif; margin-top: .5rem;">পাসওয়ার্ড রিসেট</h2>
        <p style="color: var(--text-secondary); margin-top: .3rem; font-size: 14px;">আপনার নম্বরে OTP পাঠানো হবে</p>
    </div>

    @if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>⚠️ {{ $e }}</div>@endforeach</div>@endif

    <div class="card">
        <form method="POST" action="{{ route('password.otp') }}">
            @csrf
            <div class="form-group">
                <label>মোবাইল নম্বর</label>
                <input type="tel" name="mobile" value="{{ old('mobile') }}" placeholder="01XXXXXXXXX" maxlength="11" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-block">OTP পাঠান</button>
        </form>
        <div class="auth-link"><a href="{{ route('login') }}">← লগইনে ফিরুন</a></div>
    </div>
</div>
@endsection
