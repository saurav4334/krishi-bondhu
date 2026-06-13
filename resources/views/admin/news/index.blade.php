@extends('layouts.app')

@section('title', 'সংবাদ ব্যবস্থাপনা')

@section('content')
<style>
    .admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .admin-table th { text-align: left; padding: .6rem .7rem; background: var(--green-50); color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
    .admin-table td { padding: .6rem .7rem; border-bottom: 1px solid var(--border); }
    .inline-actions form { display: inline; }
    .inline-actions button, .inline-actions a { background: none; font-size: 16px; padding: 2px 4px; }
</style>

<a href="{{ route('admin.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← অ্যাডমিন</a>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <h2 class="page-title" style="margin: 0; border: none; padding: 0;">📰 সংবাদ ব্যবস্থাপনা</h2>
    <a href="{{ route('admin.news.create') }}" class="btn btn-primary">+ নতুন</a>
</div>

<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem;">📂 ক্যাটাগরি</h3>
    <form method="POST" action="{{ route('admin.news.categories.store') }}" style="display: flex; gap: .5rem; margin-bottom: .75rem;">
        @csrf
        <input type="text" name="name" placeholder="ক্যাটাগরির নাম" required style="flex: 1; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
        <button class="btn btn-primary">+ যোগ</button>
    </form>
    <div style="display: flex; flex-wrap: wrap; gap: .4rem;">
        @foreach($categories as $cat)
            <span class="badge badge-green" style="display: inline-flex; align-items: center; gap: 5px;">
                {{ $cat->name }} ({{ $cat->posts_count }})
                <form method="POST" action="{{ route('admin.news.categories.delete', $cat) }}" onsubmit="return confirm('মুছবেন?')" style="display: inline;">@csrf @method('DELETE')<button style="background: none; color: var(--red-500); font-size: 13px;">✕</button></form>
            </span>
        @endforeach
    </div>
</div>

<div class="card">
    <table class="admin-table">
        <thead><tr><th>শিরোনাম</th><th>ক্যাটাগরি</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr></thead>
        <tbody>
            @forelse($posts as $post)
                <tr>
                    <td>{{ $post->title }} @if($post->is_important)<span class="badge badge-red">★</span>@endif</td>
                    <td>{{ $post->category->name ?? '-' }}</td>
                    <td>@if($post->status === 'published')<span class="badge badge-green">প্রকাশিত</span>@else<span class="badge badge-amber">খসড়া</span>@endif</td>
                    <td class="inline-actions" style="white-space: nowrap;">
                        <a href="{{ route('admin.news.edit', $post) }}" title="এডিট">✏️</a>
                        <form method="POST" action="{{ route('admin.news.destroy', $post) }}" onsubmit="return confirm('মুছবেন?')">@csrf @method('DELETE')<button title="মুছুন">🗑️</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1rem;">কোনো সংবাদ নেই</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top: .75rem;">{{ $posts->links() }}</div>
</div>
@endsection
