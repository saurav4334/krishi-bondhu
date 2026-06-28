@extends('layouts.app')

@section('title', 'ফসল বিক্রয়')

@section('content')
<style>
    .chips { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .35rem; margin-bottom: .75rem; }
    .chip { flex-shrink: 0; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 500; background: var(--card); border: 1px solid var(--border); color: var(--text-secondary); white-space: nowrap; }
    .chip.active { background: var(--green-500); color: #fff; border-color: var(--green-500); }
    .filter-row { display: flex; gap: .5rem; margin-bottom: 1rem; }
    .filter-row input, .filter-row select { flex: 1; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); background: #fff; font-size: 14px; }
    .post-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 1rem; box-shadow: var(--shadow); position: relative; }
    .post-card .featured-tag { position: absolute; top: 10px; left: 10px; background: var(--amber-400); color: #5a3e00; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px; z-index: 2; }
    .post-card .post-header { padding: 1rem; display: flex; align-items: center; gap: .75rem; }
    .post-card .seller-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--green-100); display: flex; align-items: center; justify-content: center; font-weight: 600; color: var(--green-600); font-size: 16px; flex-shrink: 0; }
    .post-card .seller-info h4 { font-size: 15px; font-weight: 600; font-family: 'Hind Siliguri', sans-serif; }
    .post-card .seller-info p { font-size: 12px; color: var(--text-muted); }
    .post-card .post-body { padding: 0 1rem 1rem; }
    .post-card .crop-title { font-size: 1.15rem; color: var(--green-700); margin-bottom: .4rem; }
    .post-card .post-meta { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: .6rem; }
    .post-card .post-actions { display: flex; gap: .5rem; margin-top: .75rem; }
    .post-card .post-actions a { flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 9px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 500; }
    .btn-call { background: var(--green-50); color: var(--green-600); border: 1px solid var(--green-200); }
    .btn-call:hover { background: var(--green-100); }
    .post-image { width: 100%; max-height: 220px; object-fit: cover; }
</style>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
    <h2 class="page-title" style="margin: 0; border: none; padding: 0;">🌾 ফসল বিক্রয়</h2>
    <a href="{{ route('market.create') }}" class="btn btn-primary">+ পোস্ট</a>
</div>

{{-- Category chips --}}
<div class="chips">
    <a href="{{ route('market.index', array_merge($filters, ['category' => null])) }}" class="chip {{ empty($filters['category']) ? 'active' : '' }}">সব</a>
    @foreach($categories as $cat)
        <a href="{{ route('market.index', array_merge($filters, ['category' => $cat->slug])) }}" class="chip {{ ($filters['category'] ?? null) === $cat->slug ? 'active' : '' }}">{{ $cat->icon }} {{ $cat->name }}</a>
    @endforeach
</div>

{{-- Search + district filter --}}
<form method="GET" class="filter-row">
    <input type="hidden" name="category" value="{{ $filters['category'] ?? '' }}">
    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="🔍 পণ্য খুঁজুন...">
    <select name="district">
        <option value="">সব জেলা</option>
        @foreach($districts as $d)
            <option value="{{ $d->bn_name }}" {{ ($filters['district'] ?? null) === $d->bn_name ? 'selected' : '' }}>{{ $d->bn_name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary" style="flex: 0 0 auto;">খুঁজুন</button>
</form>

@forelse($posts as $post)
    <div class="post-card">
        @if($post->featured)<span class="featured-tag">⭐ ফিচার্ড</span>@endif
        <div class="post-header">
            <div class="seller-avatar">{{ mb_substr($post->user->name ?? 'অ', 0, 1) }}</div>
            <div class="seller-info" style="flex: 1;">
                <h4>{{ $post->user->name ?? 'অজানা' }}</h4>
                <p>{{ $post->created_at->diffForHumans() }}</p>
            </div>
            @if(auth()->id() === $post->user_id || auth()->user()->isAdmin())
                <form method="POST" action="{{ route('market.destroy', $post) }}" onsubmit="return confirm('এই পোস্ট মুছে ফেলবেন?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">মুছুন</button>
                </form>
            @endif
        </div>

        @if($post->image)
            <img src="{{ image_url($post->image, 'images/no-product.png') }}" alt="{{ $post->crop_name }}" class="post-image" loading="lazy"
                 onerror="this.onerror=null; this.src='{{ asset('images/no-product.png') }}';">
        @endif

        <div class="post-body">
            <h3 class="crop-title">{{ $post->category->icon ?? '🌱' }} {{ $post->crop_name }}</h3>
            <div class="post-meta">
                <span class="badge badge-green">৳{{ number_format($post->price, 0) }}</span>
                <span class="badge badge-amber">📦 {{ $post->quantity }}</span>
                <span class="badge badge-sky">📍 {{ $post->location }}{{ $post->upazila ? ', ' . $post->upazila : '' }}</span>
                @if($post->condition_label)<span class="badge" style="background: var(--brown-50); color: #8a5a2b;">🏷️ {{ $post->condition_label }}</span>@endif
                @if($post->category)<span class="badge" style="background: var(--green-50); color: var(--green-600);">{{ $post->category->name }}</span>@endif
            </div>
            @if($post->description)
                <p style="font-size: 13px; color: var(--text-secondary);">{{ $post->description }}</p>
            @endif
            <div class="post-actions">
                <a href="tel:{{ $post->mobile }}" class="btn-call">📞 কল করুন</a>
                <a href="https://wa.me/88{{ $post->mobile }}" target="_blank" class="btn-call">💬 WhatsApp</a>
            </div>
        </div>
    </div>
@empty
    <div class="card" style="text-align: center; color: var(--text-muted);">
        <p style="font-size: 2rem;">🌾</p>
        <p>কোনো পণ্য পাওয়া যায়নি</p>
        <a href="{{ route('market.create') }}" class="btn btn-primary" style="margin-top: .75rem;">প্রথম পোস্ট করুন</a>
    </div>
@endforelse

{{ $posts->links() }}
@endsection
