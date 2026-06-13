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

{{-- Module management shortcuts --}}
<div class="grid-3" style="margin-bottom: 1rem;">
    <a href="{{ route('admin.marketplace.index') }}" class="btn btn-secondary" style="justify-content: center;">🛒 মার্কেটপ্লেস</a>
    <a href="{{ route('admin.news.index') }}" class="btn btn-secondary" style="justify-content: center;">📰 সংবাদ</a>
    <a href="{{ route('admin.weather.index') }}" class="btn btn-secondary" style="justify-content: center;">🌦️ আবহাওয়া</a>
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

{{-- District Management --}}
<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; font-family: 'Hind Siliguri', sans-serif; font-weight: 600;">🗺️ জেলা ব্যবস্থাপনা</h3>
    <form method="POST" action="{{ route('admin.districts.store') }}" style="margin-bottom: 1rem;">
        @csrf
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group" style="margin-bottom: .5rem;">
                <label>বিভাগ</label>
                <select name="division_id" required>
                    <option value="">নির্বাচন করুন</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div->id }}">{{ $div->bn_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: .5rem;">
                <label>জেলার নাম (বাংলা)</label>
                <input type="text" name="bn_name" placeholder="যেমন: নড়াইল" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">+ জেলা যুক্ত করুন</button>
    </form>

    <div style="max-height: 320px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--radius-sm);">
        <table class="admin-table">
            <thead>
                <tr><th>জেলা</th><th>বিভাগ</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr>
            </thead>
            <tbody>
                @foreach($districts as $d)
                    <tr>
                        <td>{{ $d->bn_name }}</td>
                        <td>{{ $d->division->bn_name ?? '-' }}</td>
                        <td>
                            @if($d->status === 'active')
                                <span class="badge badge-green">সক্রিয়</span>
                            @else
                                <span class="badge badge-red">নিষ্ক্রিয়</span>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            <button type="button" onclick="editDistrict({{ $d->id }}, '{{ $d->bn_name }}')" title="সম্পাদনা" style="background: none; font-size: 15px;">✏️</button>
                            <form method="POST" action="{{ route('admin.districts.toggle', $d) }}" style="display: inline;">
                                @csrf @method('PATCH')
                                <button type="submit" title="সক্রিয়/নিষ্ক্রিয়" style="background: none; font-size: 15px;">{{ $d->status === 'active' ? '🚫' : '✅' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.districts.delete', $d) }}" style="display: inline;" onsubmit="return confirm('এই জেলা মুছে ফেলবেন?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="মুছুন" style="background: none; font-size: 15px;">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- shared hidden form for inline edit --}}
    <form method="POST" id="edit-district-form" action="">
        @csrf @method('PUT')
        <input type="hidden" name="bn_name" id="edit-district-name">
    </form>
</div>

{{-- Users (with district filter) --}}
<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; font-family: 'Hind Siliguri', sans-serif; font-weight: 600;">
        👥 ব্যবহারকারী @if($districtFilter)<span style="font-size: 13px; color: var(--text-muted);">— {{ $districtFilter }}</span>@endif
    </h3>
    <form method="GET" action="{{ route('admin.index') }}" style="display: flex; gap: .5rem; margin-bottom: .75rem;">
        <select name="district" onchange="this.form.submit()" style="flex: 1; padding: 8px 10px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
            <option value="">সকল জেলা অনুযায়ী ফিল্টার</option>
            @foreach($districts as $d)
                <option value="{{ $d->bn_name }}" {{ $districtFilter === $d->bn_name ? 'selected' : '' }}>{{ $d->bn_name }}</option>
            @endforeach
        </select>
        @if($districtFilter)<a href="{{ route('admin.index') }}" class="btn btn-secondary" style="padding: 8px 12px;">✕</a>@endif
    </form>
    @if($recent_users->count())
    <table class="admin-table">
        <thead>
            <tr><th>নাম</th><th>মোবাইল</th><th>জেলা</th><th>ভূমিকা</th></tr>
        </thead>
        <tbody>
            @foreach($recent_users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->mobile }}</td>
                    <td>{{ $u->district ?? '-' }}</td>
                    <td>{{ $u->getRoleLabel() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p style="text-align: center; color: var(--text-muted); font-size: 14px; padding: .5rem;">এই জেলায় কোনো ব্যবহারকারী নেই</p>
    @endif
</div>

@push('scripts')
<script>
    function editDistrict(id, current) {
        const name = prompt('নতুন জেলার নাম:', current);
        if (name === null || name.trim() === '') return;
        const form = document.getElementById('edit-district-form');
        form.action = '{{ url('admin/districts') }}/' + id;
        document.getElementById('edit-district-name').value = name.trim();
        form.submit();
    }
</script>
@endpush

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
