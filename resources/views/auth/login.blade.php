@extends('layouts.app')

@section('title', 'লগইন')

@section('content')
<style>
    .auth-page { max-width: 440px; margin: 0 auto; padding: 2rem 1rem; }
    .auth-hero { text-align: center; padding: 2rem 0 1.5rem; }
    .auth-hero .emoji { font-size: 3.5rem; display: block; margin-bottom: .5rem; }
    .auth-hero h1 { color: var(--green-600); font-size: 1.8rem; font-family: 'Noto Serif Bengali', serif; }
    .auth-hero p { color: var(--text-secondary); margin-top: .3rem; }
    .auth-link { text-align: center; margin-top: 1rem; color: var(--text-secondary); font-size: 14px; }
    .auth-link a { color: var(--green-600); font-weight: 500; }
    .demo-info { background: var(--amber-50); border: 1px solid var(--amber-100); border-radius: var(--radius-sm); padding: .8rem 1rem; font-size: 13px; color: #7a5200; margin-top: 1rem; }
</style>

<div class="auth-page">
    <div class="auth-hero">
        <img src="{{ asset('images/logo.png') }}" alt="কৃষি-বন্ধু — কৃষকের ডিজিটাল সহায়ক" style="width: min(78%, 260px); height: auto;">
    </div>

    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>⚠️ {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <h2 style="font-size: 1.1rem; color: var(--green-700); margin-bottom: 1rem;">প্রবেশ করুন</h2>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label>মোবাইল নম্বর</label>
                <input type="tel" name="mobile" value="{{ old('mobile') }}" placeholder="01XXXXXXXXX" maxlength="11" required autofocus>
            </div>
            <div class="form-group">
                <label>পাসওয়ার্ড</label>
                <input type="password" name="password" placeholder="পাসওয়ার্ড দিন" required>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="remember" id="remember" style="width: auto;">
                <label for="remember" style="margin: 0; font-weight: 400;">মনে রাখুন</label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">লগইন করুন</button>
        </form>

        <div class="auth-link">
            অ্যাকাউন্ট নেই? <a href="{{ route('register') }}">নিবন্ধন করুন</a>
        </div>

        <div class="demo-info">
            <strong>Demo অ্যাকাউন্ট:</strong><br>
            👨‍🌾 কৃষক: <code>01712345678</code><br>
            👨‍🔬 বিশেষজ্ঞ: <code>01812345678</code><br>
            ⚙️ Admin: <code>01912345678</code><br>
            <strong>পাসওয়ার্ড:</strong> <code>password</code>
        </div>
    </div>
</div>
@endsection
