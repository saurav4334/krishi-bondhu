@extends('layouts.app')

@section('title', 'কৃষি সরঞ্জাম ব্যবস্থাপনা')

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
    .cat-main { font-weight: 600; }
    .cat-sub td:first-child { padding-left: 1.5rem; color: var(--text-secondary); }
</style>

<a href="{{ route('admin.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← অ্যাডমিন</a>
<h2 class="page-title">🚜 কৃষি সরঞ্জাম ব্যবস্থাপনা</h2>

{{-- Category management --}}
<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem;">📂 ক্যাটাগরি</h3>
    <form method="POST" action="{{ route('admin.equipment.categories.store') }}" style="display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: .75rem;">
        @csrf
        <select name="parent_id" style="flex: 1 1 130px; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
            <option value="">— মূল ক্যাটাগরি —</option>
            @foreach($mains as $m)
                <option value="{{ $m->id }}">{{ $m->icon }} {{ $m->name }}</option>
            @endforeach
        </select>
        <input type="text" name="name" placeholder="ক্যাটাগরির নাম" required style="flex: 2 1 130px; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
        <input type="text" name="icon" placeholder="🚜" maxlength="16" style="flex: 0 1 70px; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
        <button class="btn btn-primary">+ যোগ</button>
    </form>
    <table class="admin-table">
        <thead><tr><th>ক্যাটাগরি</th><th>পণ্য</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr></thead>
        <tbody>
            @foreach($categoryTree as $main)
                @php $rows = collect([$main])->concat($main->children); @endphp
                @foreach($rows as $cat)
                    <tr class="{{ $cat->parent_id ? 'cat-sub' : 'cat-main' }}">
                        <td>{{ $cat->icon }} {{ $cat->name }}</td>
                        <td>{{ $cat->products_count }}</td>
                        <td>@if($cat->status === 'active')<span class="badge badge-green">সক্রিয়</span>@else<span class="badge badge-red">নিষ্ক্রিয়</span>@endif</td>
                        <td class="inline-actions" style="white-space: nowrap;">
                            <form method="POST" action="{{ route('admin.equipment.categories.toggle', $cat) }}">@csrf @method('PATCH')<button title="সক্রিয়/নিষ্ক্রিয়">{{ $cat->status === 'active' ? '🚫' : '✅' }}</button></form>
                            <form method="POST" action="{{ route('admin.equipment.categories.delete', $cat) }}" onsubmit="return confirm('{{ $cat->parent_id ? 'মুছবেন?' : 'মূল ক্যাটাগরি ও এর সব উপ-ক্যাটাগরি মুছবেন?' }}')">@csrf @method('DELETE')<button title="মুছুন">🗑️</button></form>
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

{{-- Listings moderation --}}
<div class="card">
    <h3 style="font-size: 1rem; color: var(--green-700); margin-bottom: .75rem;">📋 পণ্য মডারেশন</h3>
    <div class="seg">
        @foreach(['pending' => 'অপেক্ষমাণ', 'approved' => 'অনুমোদিত', 'all' => 'সব'] as $k => $lbl)
            <a href="{{ route('admin.equipment.index', ['filter' => $k]) }}" class="{{ $filter === $k ? 'active' : '' }}">{{ $lbl }}</a>
        @endforeach
    </div>
    <table class="admin-table">
        <thead><tr><th>পণ্য</th><th>বিক্রেতা</th><th>দাম</th><th>অ্যাকশন</th></tr></thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>
                        <a href="{{ route('equipment.show', $product) }}" style="color: var(--green-600);">{{ $product->category->icon ?? '🚜' }} {{ $product->name }}</a>
                        @if($product->featured)<span class="badge badge-amber">⭐</span>@endif
                        @if(! $product->approved)<span class="badge badge-red">অপেক্ষমাণ</span>@endif
                        <div style="font-size: 11px; color: var(--text-muted);">📍 {{ $product->location }} · 📞 {{ $product->mobile }}</div>
                    </td>
                    <td>{{ $product->user->name ?? '-' }}</td>
                    <td>৳{{ number_format($product->price, 0) }}</td>
                    <td class="inline-actions" style="white-space: nowrap;">
                        @if(! $product->approved)
                            <form method="POST" action="{{ route('admin.equipment.approve', $product) }}">@csrf @method('PATCH')<button title="অনুমোদন">✅</button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.equipment.feature', $product) }}">@csrf @method('PATCH')<button title="ফিচার্ড">⭐</button></form>
                        <form method="POST" action="{{ route('admin.equipment.reject', $product) }}" onsubmit="return confirm('বাতিল করবেন?')">@csrf @method('DELETE')<button title="বাতিল">🗑️</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1rem;">কোনো পণ্য নেই</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top: .75rem;">{{ $products->links() }}</div>
</div>
@endsection
