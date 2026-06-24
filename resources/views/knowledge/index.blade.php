@extends('layouts.app')

@section('title', 'কৃষি জ্ঞানভান্ডার')

@section('content')
<style>
    .kb-search { display: flex; gap: .5rem; margin-bottom: 1rem; }
    .kb-search input { flex: 1; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 15px; }
    .chips { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .35rem; margin-bottom: 1rem; }
    .chip { flex-shrink: 0; padding: 6px 13px; border-radius: 999px; font-size: 13px; font-weight: 500; background: var(--card); border: 1px solid var(--border); color: var(--text-secondary); white-space: nowrap; }
    .chip.active { background: var(--green-500); color: #fff; border-color: var(--green-500); }
    .kb-item { display: block; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: .9rem 1rem; margin-bottom: .6rem; box-shadow: var(--shadow); }
    .kb-item h4 { font-size: 15px; color: var(--green-700); font-family: 'Hind Siliguri', sans-serif; font-weight: 600; }
    .kb-item p { font-size: 13px; color: var(--text-secondary); margin-top: 3px; }
    .kb-item .meta { font-size: 11px; color: var(--text-muted); margin-top: 5px; }
    .section-title { font-size: 1rem; color: var(--text-secondary); font-weight: 600; margin: .75rem 0 .5rem; }
</style>

<h2 class="page-title">📚 কৃষি জ্ঞানভান্ডার</h2>
<p style="font-size: 13px; color: var(--text-muted); margin-bottom: 1rem;">কৃষি বিষয়ক সাধারণ প্রশ্নের উত্তর খুঁজুন — তাৎক্ষণিক, বিনামূল্যে।</p>

<form method="GET" class="kb-search">
    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="🔍 প্রশ্ন বা বিষয় লিখুন...">
    <button type="submit" class="btn btn-primary">খুঁজুন</button>
</form>

{{-- Categories --}}
<div class="chips">
    <a href="{{ route('knowledge.index') }}" class="chip {{ empty($filters['category']) ? 'active' : '' }}">সব</a>
    @foreach($categories as $cat)
        <a href="{{ route('knowledge.index', ['category' => $cat->slug]) }}" class="chip {{ ($filters['category'] ?? null) === $cat->slug ? 'active' : '' }}">{{ $cat->icon }} {{ $cat->name }} ({{ $cat->articles_count }})</a>
    @endforeach
</div>

{{-- Popular (only on the default view) --}}
@if(empty($filters['q']) && empty($filters['category']) && $popular->count())
    <div class="section-title">🔥 জনপ্রিয় প্রশ্ন</div>
    @foreach($popular as $p)
        <a href="{{ route('knowledge.show', $p) }}" class="kb-item">
            <h4>{{ $p->question }}</h4>
            <div class="meta">{{ $p->category->icon ?? '📚' }} {{ $p->category->name ?? '' }} · 👁️ {{ $p->views_count }}</div>
        </a>
    @endforeach
    <div class="section-title">📋 সব প্রশ্ন</div>
@endif

@forelse($articles as $article)
    <a href="{{ route('knowledge.show', $article) }}" class="kb-item">
        <h4>{{ $article->question }}</h4>
        <p>{{ \Illuminate\Support\Str::limit(strip_tags($article->answer), 90) }}</p>
        <div class="meta">{{ $article->category->icon ?? '📚' }} {{ $article->category->name ?? '' }} · 👁️ {{ $article->views_count }} · 👍 {{ $article->helpful_count }}</div>
    </a>
@empty
    <div class="card" style="text-align: center; color: var(--text-muted);">
        <p style="font-size: 2rem;">🔍</p>
        <p>কোনো উত্তর পাওয়া যায়নি। 🤖 কৃষি AI বাটনে প্রশ্ন করুন।</p>
    </div>
@endforelse

{{ $articles->links() }}
@endsection
