@extends('layouts.app')

@section('title', 'কৃষি সংবাদ')

@section('content')
<style>
    .chips { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .35rem; margin-bottom: 1rem; }
    .chip { flex-shrink: 0; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 500; background: var(--card); border: 1px solid var(--border); color: var(--text-secondary); white-space: nowrap; }
    .chip.active { background: var(--green-500); color: #fff; border-color: var(--green-500); }
    .alert-banner { display: flex; align-items: center; gap: .6rem; padding: .7rem .9rem; background: var(--red-50); border: 1px solid var(--red-100); border-radius: var(--radius-sm); margin-bottom: .6rem; }
    .alert-banner .tag { background: var(--red-500); color: #fff; font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 999px; flex-shrink: 0; }
    .alert-banner h4 { font-size: 14px; color: var(--red-500); font-family: 'Hind Siliguri', sans-serif; }
    .news-card { display: flex; gap: .85rem; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: .85rem; box-shadow: var(--shadow); }
    .news-card .thumb { width: 110px; flex-shrink: 0; background: var(--green-50); display: flex; align-items: center; justify-content: center; font-size: 2rem; }
    .news-card .thumb img { width: 100%; height: 100%; object-fit: cover; }
    .news-card .body { padding: .8rem .9rem .8rem 0; flex: 1; min-width: 0; }
    .news-card .meta { display: flex; gap: .35rem; flex-wrap: wrap; margin-bottom: .35rem; }
    .news-card h3 { font-size: 15px; color: var(--text-primary); font-family: 'Hind Siliguri', sans-serif; font-weight: 600; line-height: 1.4; }
    .news-card p.excerpt { font-size: 12.5px; color: var(--text-muted); margin-top: .25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .news-card time { font-size: 11px; color: var(--text-muted); display: block; margin-top: .35rem; }
</style>

<h2 class="page-title">📰 কৃষি সংবাদ ও আপডেট</h2>

@foreach($important as $alert)
    <a href="{{ route('news.show', $alert) }}" class="alert-banner">
        <span class="tag">গুরুত্বপূর্ণ</span>
        <h4>{{ $alert->title }}</h4>
    </a>
@endforeach

<form method="GET" style="display: flex; gap: .5rem; margin-bottom: .85rem;">
    @if($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
    <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="🔍 সংবাদ খুঁজুন..." style="flex: 1; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
    <button type="submit" class="btn btn-primary">খুঁজুন</button>
</form>

<div class="chips">
    <a href="{{ route('news.index', array_filter(['q' => $search ?? null])) }}" class="chip {{ empty($activeCategory) ? 'active' : '' }}">সব</a>
    @foreach($categories as $cat)
        <a href="{{ route('news.index', array_merge(array_filter(['q' => $search ?? null]), ['category' => $cat->slug])) }}" class="chip {{ $activeCategory === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
    @endforeach
</div>

@forelse($posts as $post)
    <a href="{{ route('news.show', $post) }}" class="news-card">
        <div class="thumb">
            <img src="{{ safe_image_url($post->image, 'images/news/default.jpg') }}" alt="" loading="lazy"
                 onerror="this.onerror=null; this.src='{{ asset('images/news/default.jpg') }}';">
        </div>
        <div class="body">
            <div class="meta">
                <span class="badge badge-green">{{ $post->category->name ?? '' }}</span>
                @if($post->is_important)<span class="badge badge-red">গুরুত্বপূর্ণ</span>@endif
                @if($post->district)<span class="badge badge-sky">📍 {{ $post->district }}</span>@endif
            </div>
            <h3>{{ $post->title }}</h3>
            <p class="excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($post->description), 120) }}</p>
            <time>{{ optional($post->published_at)->diffForHumans() ?? $post->created_at->diffForHumans() }}</time>
        </div>
    </a>
@empty
    <div class="card" style="text-align: center; color: var(--text-muted);">
        <p style="font-size: 2rem;">📰</p>
        <p>এই মুহূর্তে কোনো সংবাদ নেই</p>
    </div>
@endforelse

{{ $posts->links() }}
@endsection
