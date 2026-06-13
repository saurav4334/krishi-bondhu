@extends('layouts.app')

@section('title', 'আবহাওয়া সতর্কতা')

@section('content')
<style>
    .admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .admin-table th { text-align: left; padding: .6rem .7rem; background: var(--green-50); color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
    .admin-table td { padding: .6rem .7rem; border-bottom: 1px solid var(--border); }
</style>

<a href="{{ route('admin.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← অ্যাডমিন</a>
<h2 class="page-title">🌦️ আবহাওয়া সতর্কতা</h2>

<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem;">➕ নতুন সতর্কতা</h3>
    <form method="POST" action="{{ route('admin.weather.store') }}">
        @csrf
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>জেলা *</label>
                <select name="district" required>
                    <option value="">নির্বাচন করুন</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->bn_name }}">{{ $d->bn_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>ধরন *</label>
                <select name="alert_type" required>
                    @foreach(\App\Models\WeatherAlert::TYPES as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>শিরোনাম *</label>
            <input type="text" name="title" required placeholder="যেমন: আগামী ৪৮ ঘণ্টায় ভারী বৃষ্টি">
        </div>
        <div class="form-group">
            <label>বিবরণ *</label>
            <textarea name="description" required></textarea>
        </div>
        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>তীব্রতা *</label>
                <select name="severity" required>
                    @foreach(\App\Models\WeatherAlert::SEVERITIES as $val => $lbl)
                        <option value="{{ $val }}" {{ $val === 'moderate' ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>তারিখ *</label>
                <input type="date" name="alert_date" value="{{ now()->toDateString() }}" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">সতর্কতা যুক্ত করুন</button>
    </form>
</div>

<div class="card">
    <table class="admin-table">
        <thead><tr><th>সতর্কতা</th><th>জেলা</th><th>তারিখ</th><th></th></tr></thead>
        <tbody>
            @forelse($alerts as $alert)
                <tr>
                    <td>{{ $alert->icon }} {{ $alert->title }} <span class="badge badge-amber">{{ $alert->severity_label }}</span></td>
                    <td>{{ $alert->district }}</td>
                    <td>{{ $alert->alert_date->format('d/m/Y') }}</td>
                    <td style="text-align: right;">
                        <form method="POST" action="{{ route('admin.weather.destroy', $alert) }}" onsubmit="return confirm('মুছবেন?')">@csrf @method('DELETE')<button style="background: none; font-size: 16px;">🗑️</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1rem;">কোনো সতর্কতা নেই</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top: .75rem;">{{ $alerts->links() }}</div>
</div>
@endsection
