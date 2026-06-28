@extends('layouts.app')

@section('title', $post->title)

@section('content')
<style>
    .article-img { width: 100%; max-height: 280px; object-fit: cover; border-radius: var(--radius-lg); margin-bottom: 1rem; }
    .article-meta { display: flex; gap: .35rem; flex-wrap: wrap; margin-bottom: .6rem; }
    .article-body { font-size: 15px; color: var(--text-primary); line-height: 1.8; white-space: pre-line; }
    .related a { display: block; padding: .7rem .85rem; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-sm); margin-bottom: .5rem; font-size: 14px; font-weight: 500; color: var(--text-secondary); }
</style>

<a href="{{ route('news.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← সংবাদে ফিরুন</a>

<div class="card">
    <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="article-img" loading="lazy"
         onerror="this.onerror=null; this.src='{{ asset('images/news/default.jpg') }}';">
    <div class="article-meta">
        <span class="badge badge-green">{{ $post->category->name ?? '' }}</span>
        @if($post->is_important)<span class="badge badge-red">গুরুত্বপূর্ণ</span>@endif
        @if($post->district)<span class="badge badge-sky">📍 {{ $post->district }}</span>@endif
    </div>
    <h2 style="font-size: 1.3rem; color: var(--green-700); margin-bottom: .35rem;">{{ $post->title }}</h2>
    <time style="font-size: 12px; color: var(--text-muted);">{{ optional($post->published_at)->translatedFormat('d F Y') ?? $post->created_at->translatedFormat('d F Y') }} · 👁️ {{ $post->views_count }}</time>
    @if($post->description)<p style="font-size: 14px; color: var(--text-secondary); font-weight: 500; margin-top: .75rem;">{{ $post->description }}</p>@endif
    <div class="article-body" style="margin-top: 1rem;">{{ $post->content ?: $post->description }}</div>
</div>

@if($related->isNotEmpty())
    <div class="section-title">সম্পর্কিত সংবাদ</div>
    <div class="related">
        @foreach($related as $item)
            <a href="{{ route('news.show', $item) }}">{{ $item->title }}</a>
        @endforeach
    </div>
@endif
@endsection
