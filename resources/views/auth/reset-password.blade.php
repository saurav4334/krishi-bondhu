@extends('layouts.app')

@section('title', 'নতুন পাসওয়ার্ড')

@section('content')
<style>
    .auth-page { max-width: 440px; margin: 0 auto; padding: 2rem 1rem; }
    .auth-hero { text-align: center; padding: 1.5rem 0 1rem; }
    .auth-link { text-align: center; margin-top: 1rem; color: var(--text-secondary); font-size: 14px; }
    .auth-link a { color: var(--green-600); font-weight: 500; }
</style>

<div class="auth-page">
    <div class="auth-hero">
        <img src="{{ asset('images/logo.png') }}" alt="কৃষি-বন্ধু" style="width: min(52%, 150px); height: auto;">
        <h2 style="color: var(--green-600); font-family: 'Noto Serif Bengali', serif; margin-top: .5rem;">নতুন পাসওয়ার্ড</h2>
        <p style="color: var(--text-secondary); margin-top: .3rem; font-size: 14px;"><strong>{{ $mobile }}</strong> এ পাঠানো OTP দিন</p>
    </div>

    @if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>⚠️ {{ $e }}</div>@endforeach</div>@endif

    <div class="card">
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <div class="form-group">
                <label>OTP কোড</label>
                <input type="text" name="otp" maxlength="6" inputmode="numeric" placeholder="------" required style="text-align: center; letter-spacing: .4rem; font-weight: 600;">
            </div>
            <div class="form-group">
                <label>নতুন পাসওয়ার্ড</label>
                <input type="password" name="password" placeholder="কমপক্ষে ৬ অক্ষর" required>
            </div>
            <div class="form-group">
                <label>পাসওয়ার্ড নিশ্চিত করুন</label>
                <input type="password" name="password_confirmation" placeholder="আবার লিখুন" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">পাসওয়ার্ড পরিবর্তন করুন</button>
        </form>
        <div class="auth-link"><a href="{{ route('password.request') }}">নতুন OTP চান?</a></div>
    </div>
</div>
@endsection
