@extends('layouts.app')

@section('title', 'প্রোফাইল')

@section('content')
<style>
    .profile-header { text-align: center; padding: 1.5rem 0 1rem; }
    .avatar-circle { width: 80px; height: 80px; border-radius: 50%; background: var(--green-100); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto .75rem; border: 3px solid var(--green-200); }
    .profile-name { font-size: 1.3rem; color: var(--green-700); }
    .profile-role { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
    .profile-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; margin: 1rem 0; }
    .p-stat { background: var(--green-50); border: 1px solid var(--green-100); border-radius: var(--radius); padding: .85rem; text-align: center; }
    .p-stat .n { font-size: 1.5rem; font-weight: 700; color: var(--green-500); }
    .p-stat .l { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
</style>

<div class="profile-header">
    <div class="avatar-circle">
        @if($user->role === 'admin') ⚙️
        @elseif($user->role === 'expert') 👨‍🔬
        @else 🧑‍🌾
        @endif
    </div>
    <h2 class="profile-name">{{ $user->name }}</h2>
    <p class="profile-role">{{ $user->getRoleLabel() }}</p>
    <p style="color: var(--text-muted); font-size: 13px; margin-top: 3px;">📱 {{ $user->mobile }}</p>
</div>

<div class="profile-stats">
    <div class="p-stat"><div class="n">{{ $stats['scans'] }}</div><div class="l">মোট স্ক্যান</div></div>
    <div class="p-stat"><div class="n">{{ $stats['posts'] }}</div><div class="l">ফসলের পোস্ট</div></div>
</div>

<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; font-family: 'Hind Siliguri', sans-serif; font-weight: 600;">প্রোফাইল সম্পাদনা</h3>
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>নাম</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="form-group">
            <label>জেলা</label>
            <select name="district">
                <option value="">জেলা নির্বাচন করুন</option>
                @foreach(['ঢাকা','চট্টগ্রাম','রাজশাহী','খুলনা','বরিশাল','সিলেট','ময়মনসিংহ','রংপুর','বগুড়া','কুমিল্লা','নারায়ণগঞ্জ','গাজীপুর','যশোর','দিনাজপুর','ফরিদপুর'] as $d)
                    <option value="{{ $d }}" {{ old('district', $user->district) === $d ? 'selected' : '' }}>{{ $d }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">সংরক্ষণ করুন</button>
    </form>
</div>

<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; font-family: 'Hind Siliguri', sans-serif; font-weight: 600;">🔒 পাসওয়ার্ড পরিবর্তন</h3>
    <form method="POST" action="{{ route('profile.password') }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>বর্তমান পাসওয়ার্ড</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="form-group">
            <label>নতুন পাসওয়ার্ড</label>
            <input type="password" name="password" placeholder="কমপক্ষে ৬ অক্ষর" required>
        </div>
        <div class="form-group">
            <label>নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
            <input type="password" name="password_confirmation" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">পাসওয়ার্ড পরিবর্তন করুন</button>
    </form>
</div>

@if($user->isAdmin())
    <a href="{{ route('admin.index') }}" class="btn btn-secondary btn-block" style="background: var(--amber-50); border-color: var(--amber-100); color: #7a5200; margin-top: .5rem;">
        ⚙️ অ্যাডমিন প্যানেল
    </a>
@endif
@endsection
