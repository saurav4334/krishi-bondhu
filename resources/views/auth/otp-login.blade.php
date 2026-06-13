@extends('layouts.app')

@section('title', 'OTP দিয়ে লগইন')

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
        <h2 style="color: var(--green-600); font-family: 'Noto Serif Bengali', serif; margin-top: .5rem;">OTP দিয়ে লগইন</h2>
    </div>

    @if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>⚠️ {{ $e }}</div>@endforeach</div>@endif

    <div class="card">
        <form method="POST" action="{{ route('login.otp.send') }}">
            @csrf
            <div class="form-group">
                <label>মোবাইল নম্বর</label>
                <input type="tel" name="mobile" value="{{ old('mobile') }}" placeholder="01XXXXXXXXX" maxlength="11" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-block">OTP পাঠান</button>
        </form>
        <div class="auth-link">
            <a href="{{ route('login') }}">← পাসওয়ার্ড দিয়ে লগইন</a>
        </div>
    </div>
</div>
@endsection
