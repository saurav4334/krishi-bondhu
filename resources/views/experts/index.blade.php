@extends('layouts.app')

@section('title', 'কৃষি বিশেষজ্ঞ')

@section('content')
<style>
    .expert-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1rem; display: flex; gap: 1rem; align-items: flex-start; margin-bottom: .85rem; box-shadow: var(--shadow); }
    .expert-avatar { width: 52px; height: 52px; border-radius: 50%; background: var(--sky-100); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
    .expert-info h3 { font-size: 16px; font-family: 'Hind Siliguri', sans-serif; font-weight: 600; color: var(--green-700); }
    .expert-info p { font-size: 13px; color: var(--text-secondary); margin-top: 2px; }
    .expert-info .meta { font-size: 12px; color: var(--text-muted); margin-top: .3rem; display: flex; flex-wrap: wrap; gap: .4rem; }
    .expert-actions { margin-top: .6rem; display: flex; gap: .5rem; }
</style>

<h2 class="page-title">👨‍🔬 কৃষি বিশেষজ্ঞ</h2>

@php $avatars = ['👨‍🔬', '👩‍🔬', '👨‍🌾', '👩‍🌾']; @endphp

@forelse($experts as $i => $expert)
    <div class="expert-card">
        <div class="expert-avatar">{{ $avatars[$i % 4] }}</div>
        <div style="flex: 1;">
            <div class="expert-info">
                <h3>{{ $expert->name }}</h3>
                <p>{{ $expert->specialization }}</p>
                <div class="meta">
                    <span>📍 {{ $expert->district }}</span>
                    @if($expert->availability)<span>⏰ {{ $expert->availability }}</span>@endif
                    @if($expert->experience)<span>🏅 {{ $expert->experience }}</span>@endif
                </div>
            </div>
            <div class="expert-actions">
                <a href="tel:{{ $expert->mobile }}" class="btn btn-primary" style="padding: 7px 14px; font-size: 13px;">📞 কল করুন</a>
                <a href="https://wa.me/88{{ $expert->mobile }}" target="_blank" class="btn btn-secondary" style="padding: 7px 14px; font-size: 13px;">💬 WhatsApp</a>
            </div>
        </div>
    </div>
@empty
    <p style="color: var(--text-muted); text-align: center; padding: 2rem;">কোনো বিশেষজ্ঞ পাওয়া যায়নি</p>
@endforelse
@endsection
