@extends('layouts.app')

@section('title', $title)

@section('content')
<style>
    .auth-page { max-width: 440px; margin: 0 auto; padding: 2rem 1rem; }
    .auth-hero { text-align: center; padding: 1.5rem 0 1rem; }
    .otp-input { text-align: center; font-size: 1.6rem; letter-spacing: .5rem; font-weight: 600; }
    .auth-link { text-align: center; margin-top: 1rem; color: var(--text-secondary); font-size: 14px; }
    .auth-link a { color: var(--green-600); font-weight: 500; }
</style>

<div class="auth-page">
    <div class="auth-hero">
        <img src="{{ asset('images/logo.png') }}" alt="কৃষি-বন্ধু" style="width: min(52%, 150px); height: auto; margin-bottom: .5rem;">
        <h2 style="color: var(--green-600); font-family: 'Noto Serif Bengali', serif;">{{ $title }}</h2>
        <p style="color: var(--text-secondary); margin-top: .3rem; font-size: 14px;">
            <strong>{{ $mobile }}</strong> নম্বরে পাঠানো ৬-সংখ্যার কোডটি দিন
        </p>
    </div>

    @if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-error">@foreach($errors->all() as $e)<div>⚠️ {{ $e }}</div>@endforeach</div>
    @endif

    <div class="card">
        <form method="POST" action="{{ $action }}">
            @csrf
            <div class="form-group">
                <label>OTP কোড</label>
                <input type="text" name="otp" class="otp-input" maxlength="6" inputmode="numeric" pattern="[0-9]*" placeholder="------" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-block">যাচাই করুন</button>
        </form>

        <div class="auth-link">
            কোড পাননি?
            <form method="POST" action="{{ $resendAction }}" style="display: inline;">
                @csrf
                <button type="submit" style="background: none; color: var(--green-600); font-weight: 500; padding: 0;">আবার পাঠান</button>
            </form>
        </div>
    </div>
</div>
@endsection
