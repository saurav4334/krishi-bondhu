@extends('layouts.app')

@section('title', 'মার্কেটপ্লেস ব্যবস্থাপনা')

@section('content')
<style>
    .admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .admin-table th { text-align: left; padding: .6rem .7rem; background: var(--green-50); color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
    .admin-table td { padding: .6rem .7rem; border-bottom: 1px solid var(--border); }
    .seg { display: flex; gap: .4rem; margin-bottom: 1rem; }
    .seg a { padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 500; background: var(--card); border: 1px solid var(--border); color: var(--text-secondary); }
    .seg a.active { background: var(--green-500); color: #fff; border-color: var(--green-500); }
    .inline-actions form { display: inline; }
    .inline-actions button { background: none; font-size: 16px; padding: 2px 4px; }
</style>

<a href="{{ route('admin.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← অ্যাডমিন</a>
<h2 class="page-title">🛒 মার্কেটপ্লেস ব্যবস্থাপনা</h2>

{{-- Category management --}}
<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem;">📂 ক্যাটাগরি</h3>
    <form method="POST" action="{{ route('admin.marketplace.categories.store') }}" style="display: flex; gap: .5rem; margin-bottom: .75rem;">
        @csrf
        <input type="text" name="name" placeholder="ক্যাটাগরির নাম" required style="flex: 2; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
        <input type="text" name="icon" placeholder="🌱" maxlength="16" style="flex: 1; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
        <button class="btn btn-primary">+ যোগ</button>
    </form>
    <table class="admin-table">
        <thead><tr><th>ক্যাটাগরি</th><th>পণ্য</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr></thead>
        <tbody>
            @foreach($categories as $cat)
                <tr>
                    <td>{{ $cat->icon }} {{ $cat->name }}</td>
                    <td>{{ $cat->posts_count }}</td>
                    <td>@if($cat->status === 'active')<span class="badge badge-green">সক্রিয়</span>@else<span class="badge badge-red">নিষ্ক্রিয়</span>@endif</td>
                    <td class="inline-actions" style="white-space: nowrap;">
                        <form method="POST" action="{{ route('admin.marketplace.categories.toggle', $cat) }}">@csrf @method('PATCH')<button title="সক্রিয়/নিষ্ক্রিয়">{{ $cat->status === 'active' ? '🚫' : '✅' }}</button></form>
                        <form method="POST" action="{{ route('admin.marketplace.categories.delete', $cat) }}" onsubmit="return confirm('মুছবেন?')">@csrf @method('DELETE')<button title="মুছুন">🗑️</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Listings moderation --}}
<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem;">📋 বিজ্ঞাপন মডারেশন</h3>
    <div class="seg">
        @foreach(['pending' => 'অপেক্ষমাণ', 'approved' => 'অনুমোদিত', 'all' => 'সব'] as $k => $lbl)
            <a href="{{ route('admin.marketplace.index', ['filter' => $k]) }}" class="{{ $filter === $k ? 'active' : '' }}">{{ $lbl }}</a>
        @endforeach
    </div>
    <table class="admin-table">
        <thead><tr><th>পণ্য</th><th>বিক্রেতা</th><th>দাম</th><th>অ্যাকশন</th></tr></thead>
        <tbody>
            @forelse($posts as $post)
                <tr>
                    <td>
                        {{ $post->category->icon ?? '🌱' }} {{ $post->crop_name }}
                        @if($post->featured)<span class="badge badge-amber">⭐</span>@endif
                        @if(! $post->approved)<span class="badge badge-red">অপেক্ষমাণ</span>@endif
                        <div style="font-size: 11px; color: var(--text-muted);">📍 {{ $post->location }} · 📞 {{ $post->mobile }}</div>
                    </td>
                    <td>{{ $post->user->name ?? '-' }}</td>
                    <td>৳{{ number_format($post->price, 0) }}</td>
                    <td class="inline-actions" style="white-space: nowrap;">
                        @if(! $post->approved)
                            <form method="POST" action="{{ route('admin.marketplace.approve', $post) }}">@csrf @method('PATCH')<button title="অনুমোদন">✅</button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.marketplace.feature', $post) }}">@csrf @method('PATCH')<button title="ফিচার্ড">⭐</button></form>
                        <form method="POST" action="{{ route('admin.marketplace.reject', $post) }}" onsubmit="return confirm('বাতিল করবেন?')">@csrf @method('DELETE')<button title="বাতিল">🗑️</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1rem;">কোনো বিজ্ঞাপন নেই</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top: .75rem;">{{ $posts->links() }}</div>
</div>
@endsection
