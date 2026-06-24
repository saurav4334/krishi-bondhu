@extends('layouts.app')

@section('title', $article->question)

@section('content')
<a href="{{ route('knowledge.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← জ্ঞানভান্ডার</a>

<div class="card">
    @if($article->category)
        <span class="badge badge-green">{{ $article->category->icon }} {{ $article->category->name }}</span>
    @endif
    <h2 class="page-title" style="margin: .6rem 0; border: none; padding: 0;">{{ $article->question }}</h2>
    <div style="font-size: 15px; line-height: 1.7; color: var(--text-primary); white-space: pre-line;">{{ $article->answer }}</div>

    <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem; padding-top: .75rem; border-top: 1px solid var(--border);">
        <span style="font-size: 12px; color: var(--text-muted);">👁️ {{ $article->views_count }} বার দেখা · 👍 {{ $article->helpful_count }}</span>
        <form method="POST" action="{{ route('knowledge.helpful', $article) }}" style="margin-left: auto;">
            @csrf
            <button type="submit" class="btn btn-secondary" style="padding: 6px 14px;">👍 উপকারী</button>
        </form>
    </div>
</div>

@if($related->count())
    <div class="section-title" style="font-size: 1rem; color: var(--text-secondary); font-weight: 600; margin: .75rem 0 .5rem;">🔗 সম্পর্কিত প্রশ্ন</div>
    @foreach($related as $r)
        <a href="{{ route('knowledge.show', $r) }}" class="card card-sm" style="display: block; color: inherit;">
            <strong style="font-size: 14px; color: var(--green-700); font-family: 'Hind Siliguri', sans-serif;">{{ $r->question }}</strong>
        </a>
    @endforeach
@endif
@endsection
