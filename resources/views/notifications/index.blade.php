@extends('layouts.app')

@section('title', 'বিজ্ঞপ্তি')

@section('content')
<style>
    .notif-item { display: flex; gap: .75rem; align-items: flex-start; padding: .85rem 0; border-bottom: 1px solid var(--border); }
    .notif-item:last-child { border: none; }
    .notif-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--green-400); flex-shrink: 0; margin-top: 5px; }
    .notif-item h4 { font-size: 14px; font-weight: 600; font-family: 'Hind Siliguri', sans-serif; }
    .notif-item p { font-size: 13px; color: var(--text-secondary); margin-top: 2px; }
    .notif-item .time { font-size: 11px; color: var(--text-muted); margin-top: 3px; }
</style>

<h2 class="page-title">🔔 বিজ্ঞপ্তি</h2>

<div class="card card-sm">
    @forelse($notifications as $n)
        <div class="notif-item">
            <div class="notif-dot"></div>
            <div style="flex: 1;">
                <h4>{{ $n->title }}</h4>
                <p>{{ $n->message }}</p>
                <div class="time">{{ $n->created_at->diffForHumans() }}</div>
            </div>
        </div>
    @empty
        <p style="color: var(--text-muted); text-align: center; padding: 1rem;">কোনো বিজ্ঞপ্তি নেই</p>
    @endforelse
</div>

{{ $notifications->links() }}
@endsection
