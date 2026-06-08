@extends('layouts.app')

@section('title', 'নিবন্ধন')

@section('content')
<style>
    .auth-page { max-width: 440px; margin: 0 auto; padding: 2rem 1rem; }
    .auth-hero { text-align: center; padding: 1.5rem 0 1rem; }
    .auth-hero .emoji { font-size: 3rem; display: block; margin-bottom: .5rem; }
    .auth-hero h1 { color: var(--green-600); font-size: 1.6rem; font-family: 'Noto Serif Bengali', serif; }
    .auth-link { text-align: center; margin-top: 1rem; color: var(--text-secondary); font-size: 14px; }
    .auth-link a { color: var(--green-600); font-weight: 500; }
</style>

<div class="auth-page">
    <div class="auth-hero">
        <span class="emoji">🌾</span>
        <h1>নতুন অ্যাকাউন্ট</h1>
        <p style="color: var(--text-secondary); margin-top: .3rem;">কৃষি-বন্ধুতে আপনাকে স্বাগতম</p>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>⚠️ {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-group">
                <label>আপনার নাম</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="পূর্ণ নাম" required autofocus>
            </div>
            <div class="form-group">
                <label>মোবাইল নম্বর</label>
                <input type="tel" name="mobile" value="{{ old('mobile') }}" placeholder="01XXXXXXXXX" maxlength="11" required>
            </div>
            <div class="form-group">
                <label>জেলা</label>
                <select name="district" required>
                    <option value="">জেলা নির্বাচন করুন</option>
                    @foreach(['ঢাকা','চট্টগ্রাম','রাজশাহী','খুলনা','বরিশাল','সিলেট','ময়মনসিংহ','রংপুর','বগুড়া','কুমিল্লা','নারায়ণগঞ্জ','গাজীপুর','যশোর','দিনাজপুর','ফরিদপুর'] as $d)
                        <option value="{{ $d }}" {{ old('district') === $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>পাসওয়ার্ড</label>
                <input type="password" name="password" placeholder="কমপক্ষে ৬ অক্ষর" required>
            </div>
            <div class="form-group">
                <label>পাসওয়ার্ড নিশ্চিত করুন</label>
                <input type="password" name="password_confirmation" placeholder="আবার লিখুন" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">নিবন্ধন করুন</button>
        </form>

        <div class="auth-link">
            ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="{{ route('login') }}">লগইন করুন</a>
        </div>
    </div>
</div>
@endsection
