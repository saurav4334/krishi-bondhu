@extends('layouts.app')

@section('title', 'অ্যাডমিন প্যানেল')

@section('content')
<style>
    .admin-stat { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem; text-align: center; }
    .admin-stat .n { font-size: 1.8rem; font-weight: 700; color: var(--green-500); }
    .admin-stat .l { font-size: 13px; color: var(--text-muted); }
    .admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .admin-table th { text-align: left; padding: .6rem .75rem; background: var(--green-50); color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
    .admin-table td { padding: .6rem .75rem; border-bottom: 1px solid var(--border); color: var(--text-primary); }
</style>

<h2 class="page-title">⚙️ অ্যাডমিন প্যানেল</h2>

<div class="grid-2" style="margin-bottom: 1rem;">
    <div class="admin-stat"><div class="n">{{ $stats['total_users'] }}</div><div class="l">মোট কৃষক</div></div>
    <div class="admin-stat"><div class="n">{{ $stats['total_scans'] }}</div><div class="l">AI স্ক্যান</div></div>
    <div class="admin-stat"><div class="n">{{ $stats['active_posts'] }}</div><div class="l">সক্রিয় পোস্ট</div></div>
    <div class="admin-stat"><div class="n">{{ $stats['directory_experts'] }}</div><div class="l">বিশেষজ্ঞ</div></div>
</div>

<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; font-family: 'Hind Siliguri', sans-serif; font-weight: 600;">📢 বিজ্ঞপ্তি পাঠান</h3>
    <form method="POST" action="{{ route('admin.notifications.store') }}">
        @csrf
        <div class="form-group">
            <label>শিরোনাম</label>
            <input type="text" name="title" placeholder="বিজ্ঞপ্তির শিরোনাম" required>
        </div>
        <div class="form-group">
            <label>বার্তা</label>
            <textarea name="message" placeholder="বিজ্ঞপ্তির বিস্তারিত..." required></textarea>
        </div>
        <div class="form-group">
            <label>প্রাপক</label>
            <select name="user_type" required>
                <option value="all">সকল ব্যবহারকারী</option>
                <option value="farmer">শুধু কৃষক</option>
                <option value="expert">শুধু বিশেষজ্ঞ</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">পাঠান</button>
    </form>
</div>

<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; font-family: 'Hind Siliguri', sans-serif; font-weight: 600;">💰 বাজার দর আপডেট</h3>
    <form method="POST" action="{{ route('admin.prices.store') }}">
        @csrf
        <div class="form-group">
            <label>ফসলের নাম</label>
            <input type="text" name="crop_name" placeholder="যেমন: আলু, পেঁয়াজ..." required>
        </div>
        <div class="form-group">
            <label>জেলা</label>
            <select name="district" required>
                <option value="সকল">সকল জেলা</option>
                @foreach(['ঢাকা','চট্টগ্রাম','রাজশাহী','খুলনা','বরিশাল','সিলেট','ময়মনসিংহ','রংপুর'] as $d)
                    <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>দাম (টাকা)</label>
                <input type="number" name="price" placeholder="১০০" step="0.01" required>
            </div>
            <div class="form-group">
                <label>একক</label>
                <input type="text" name="unit" placeholder="কেজি / মণ" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">দাম আপডেট করুন</button>
    </form>
</div>

@if($recent_users->count())
<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; font-family: 'Hind Siliguri', sans-serif; font-weight: 600;">👥 সাম্প্রতিক ব্যবহারকারী</h3>
    <table class="admin-table">
        <thead>
            <tr><th>নাম</th><th>মোবাইল</th><th>ভূমিকা</th></tr>
        </thead>
        <tbody>
            @foreach($recent_users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->mobile }}</td>
                    <td>{{ $u->getRoleLabel() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if($recent_scans->count())
<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; font-family: 'Hind Siliguri', sans-serif; font-weight: 600;">🔬 সাম্প্রতিক স্ক্যান</h3>
    <table class="admin-table">
        <thead>
            <tr><th>রোগ</th><th>নির্ভরযোগ্যতা</th><th>ব্যবহারকারী</th></tr>
        </thead>
        <tbody>
            @foreach($recent_scans as $s)
                <tr>
                    <td>{{ $s->disease_name }}</td>
                    <td>{{ $s->confidence_score }}%</td>
                    <td>{{ $s->user->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
