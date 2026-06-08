@extends('layouts.app')

@section('title', 'ফসলের বাজার')

@section('content')
<style>
    .post-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 1rem; box-shadow: var(--shadow); }
    .post-card .post-header { padding: 1rem; display: flex; align-items: center; gap: .75rem; }
    .post-card .seller-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--green-100); display: flex; align-items: center; justify-content: center; font-weight: 600; color: var(--green-600); font-size: 16px; flex-shrink: 0; }
    .post-card .seller-info h4 { font-size: 15px; font-weight: 600; font-family: 'Hind Siliguri', sans-serif; }
    .post-card .seller-info p { font-size: 12px; color: var(--text-muted); }
    .post-card .post-body { padding: 0 1rem 1rem; }
    .post-card .crop-title { font-size: 1.15rem; color: var(--green-700); margin-bottom: .4rem; }
    .post-card .post-meta { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: .6rem; }
    .post-card .post-actions { display: flex; gap: .5rem; margin-top: .75rem; }
    .post-card .post-actions a { flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 9px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 500; cursor: pointer; }
    .btn-call { background: var(--green-50); color: var(--green-600); border: 1px solid var(--green-200); }
    .btn-call:hover { background: var(--green-100); }
    .post-image { width: 100%; max-height: 200px; object-fit: cover; }
</style>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
    <h2 class="page-title" style="margin: 0; border: none; padding: 0;">🛒 ফসলের বাজার</h2>
    <a href="{{ route('market.create') }}" class="btn btn-primary">+ পোস্ট</a>
</div>

@forelse($posts as $post)
    @php
        $emojis = ['ধান' => '🌾', 'গম' => '🌾', 'আলু' => '🥔', 'পাট' => '🌿', 'পেঁয়াজ' => '🧅', 'টমেটো' => '🍅', 'কলা' => '🍌', 'বেগুন' => '🍆', 'মরিচ' => '🌶️'];
        $emoji = '🌱';
        foreach ($emojis as $key => $val) {
            if (str_contains($post->crop_name, $key)) { $emoji = $val; break; }
        }
    @endphp
    <div class="post-card">
        <div class="post-header">
            <div class="seller-avatar">{{ mb_substr($post->user->name ?? 'অ', 0, 1) }}</div>
            <div class="seller-info" style="flex: 1;">
                <h4>{{ $post->user->name ?? 'অজানা' }}</h4>
                <p>{{ $post->created_at->diffForHumans() }}</p>
            </div>
            @if(auth()->id() === $post->user_id || auth()->user()->isAdmin())
                <form method="POST" action="{{ route('market.destroy', $post) }}" onsubmit="return confirm('এই পোস্ট মুছে ফেলবেন?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">মুছুন</button>
                </form>
            @endif
        </div>

        @if($post->image)
            <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->crop_name }}" class="post-image">
        @endif

        <div class="post-body">
            <h3 class="crop-title">{{ $emoji }} {{ $post->crop_name }}</h3>
            <div class="post-meta">
                <span class="badge badge-green">৳{{ number_format($post->price, 0) }}</span>
                <span class="badge badge-amber">📦 {{ $post->quantity }}</span>
                <span class="badge badge-sky">📍 {{ $post->location }}</span>
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
        <p>এখনো কোনো পোস্ট নেই</p>
        <a href="{{ route('market.create') }}" class="btn btn-primary" style="margin-top: .75rem;">প্রথম পোস্ট করুন</a>
    </div>
@endforelse

{{ $posts->links() }}
@endsection
