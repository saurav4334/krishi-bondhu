@extends('layouts.app')

@section('title', 'কৃষি শ্রমিক সেবা')

@section('content')
<style>
    .worker-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1rem; margin-bottom: .85rem; box-shadow: var(--shadow); display: flex; gap: .85rem; align-items: flex-start; }
    .worker-avatar { width: 52px; height: 52px; border-radius: 50%; background: var(--green-100); display: flex; align-items: center; justify-content: center; font-weight: 600; color: var(--green-600); font-size: 18px; flex-shrink: 0; overflow: hidden; }
    .worker-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .worker-info { flex: 1; min-width: 0; }
    .worker-info h4 { font-size: 15px; font-family: 'Hind Siliguri', sans-serif; font-weight: 600; }
    .worker-info .meta { display: flex; flex-wrap: wrap; gap: .35rem; margin: .4rem 0; }
    .worker-actions { display: flex; gap: .5rem; margin-top: .5rem; }
    .worker-actions a { flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 7px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 500; }
    .btn-call { background: var(--green-50); color: var(--green-600); border: 1px solid var(--green-200); }
    .btn-call:hover { background: var(--green-100); }
    .job-item { padding: .85rem; border: 1px solid var(--border); border-radius: var(--radius-sm); margin-bottom: .6rem; }
    .filter-bar { display: flex; gap: .5rem; margin-bottom: 1rem; }
    .filter-bar input, .filter-bar select { flex: 1; padding: 8px 10px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; }
</style>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; gap: .5rem;">
    <h2 class="page-title" style="margin: 0; border: none; padding: 0;">👷 কৃষি শ্রমিক</h2>
    <div style="display: flex; gap: .4rem;">
        <a href="{{ route('labor.jobs.create') }}" class="btn btn-secondary" style="padding: 8px 12px; font-size: 13px;">📋 কাজ পোস্ট</a>
        <a href="{{ route('labor.register') }}" class="btn btn-primary" style="padding: 8px 12px; font-size: 13px;">+ শ্রমিক</a>
    </div>
</div>

<form method="GET" action="{{ route('labor.index') }}" class="filter-bar">
    <input type="text" name="district" value="{{ $filters['district'] ?? '' }}" placeholder="জেলা খুঁজুন">
    <select name="skill_type" onchange="this.form.submit()">
        <option value="">সব কাজ</option>
        @foreach($skills as $skill)
            <option value="{{ $skill }}" @selected(($filters['skill_type'] ?? '') === $skill)>{{ $skill }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary" style="padding: 8px 12px;">🔍</button>
</form>

@if($jobs->count())
    <div class="section-title">📋 চলমান কাজের পোস্ট</div>
    @foreach($jobs as $job)
        <div class="job-item">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h4 style="font-size: 14px; font-family: 'Hind Siliguri', sans-serif; font-weight: 600;">{{ $job->job_type }}</h4>
                <span class="badge badge-green">৳{{ number_format($job->wage, 0) }}</span>
            </div>
            <div class="worker-info" style="margin-top: .3rem;">
                <div class="meta">
                    <span class="badge badge-sky">📍 {{ $job->location }}</span>
                    <span class="badge badge-amber">👥 {{ $job->worker_needed }} জন</span>
                    @if($job->duration)<span class="badge badge-green">⏱️ {{ $job->duration }}</span>@endif
                </div>
                <a href="tel:{{ $job->contact_number }}" class="btn-call" style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: var(--radius-sm); font-size: 13px;">📞 {{ $job->contact_number }}</a>
            </div>
        </div>
    @endforeach
@endif

<div class="section-title">🧑‍🌾 শ্রমিক তালিকা</div>
@forelse($workers as $worker)
    <div class="worker-card">
        <div class="worker-avatar">
            @if($worker->image)
                <img src="{{ asset('storage/' . $worker->image) }}" alt="">
            @else
                {{ mb_substr($worker->name, 0, 1) }}
            @endif
        </div>
        <div class="worker-info">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: .5rem;">
                <h4>{{ $worker->name }}</h4>
                @if($worker->availability_status === 'available')
                    <span class="badge badge-green">✅ মুক্ত</span>
                @else
                    <span class="badge badge-red">⛔ ব্যস্ত</span>
                @endif
            </div>
            <div class="meta">
                <span class="badge badge-amber">🛠️ {{ $worker->skill_type }}</span>
                <span class="badge badge-green">৳{{ number_format($worker->daily_wage, 0) }}/দিন</span>
            </div>
            <div class="meta">
                <span class="badge badge-sky">📍 {{ $worker->district }}{{ $worker->area ? ', ' . $worker->area : '' }}</span>
                @if($worker->experience)<span class="badge badge-amber">⭐ {{ $worker->experience }}</span>@endif
            </div>
            <div class="worker-actions">
                <a href="tel:{{ $worker->mobile }}" class="btn-call">📞 কল করুন</a>
                <a href="https://wa.me/88{{ $worker->mobile }}" target="_blank" class="btn-call">💬 WhatsApp</a>
            </div>
        </div>
    </div>
@empty
    <div class="card" style="text-align: center; color: var(--text-muted);">
        <p style="font-size: 2rem;">👷</p>
        <p>এখনো কোনো শ্রমিক নেই</p>
        <a href="{{ route('labor.register') }}" class="btn btn-primary" style="margin-top: .75rem;">শ্রমিক হিসেবে যোগ দিন</a>
    </div>
@endforelse

{{ $workers->links() }}
@endsection
