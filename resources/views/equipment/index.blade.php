@extends('layouts.app')

@section('title', 'কৃষি সরঞ্জাম')

@section('content')
<style>
    .chips { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .35rem; margin-bottom: .5rem; }
    .chip { flex-shrink: 0; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 500; background: var(--card); border: 1px solid var(--border); color: var(--text-secondary); white-space: nowrap; }
    .chip.active { background: var(--green-500); color: #fff; border-color: var(--green-500); }
    .chips.sub .chip { background: var(--green-50); border-color: var(--green-100); }
    .chips.sub .chip.active { background: var(--amber-400); color: #5a3e00; border-color: var(--amber-400); }
    .filter-row { display: flex; gap: .5rem; margin-bottom: 1rem; }
    .filter-row input, .filter-row select { flex: 1; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); background: #fff; font-size: 14px; min-width: 0; }
    .prod-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 1rem; box-shadow: var(--shadow); position: relative; }
    .prod-card .featured-tag { position: absolute; top: 10px; left: 10px; background: var(--amber-400); color: #5a3e00; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px; z-index: 2; }
    .prod-image { width: 100%; height: 190px; object-fit: cover; background: var(--green-50); display: block; }
    .prod-image-ph { width: 100%; height: 190px; background: var(--green-50); display: flex; align-items: center; justify-content: center; font-size: 3rem; }
    .prod-body { padding: .85rem 1rem 1rem; }
    .prod-title { font-size: 1.1rem; color: var(--green-700); margin-bottom: .35rem; }
    .prod-meta { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .55rem; }
    .prod-actions { display: flex; gap: .5rem; margin-top: .65rem; }
    .prod-actions a { flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 9px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 500; }
    .btn-call { background: var(--green-50); color: var(--green-600); border: 1px solid var(--green-200); }
    .btn-wa { background: #e8f8ee; color: #128c43; border: 1px solid #b6e6c6; }
    .btn-view { background: var(--green-500); color: #fff; }
</style>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
    <h2 class="page-title" style="margin: 0; border: none; padding: 0;">🛠️ কৃষি সরঞ্জাম</h2>
    <a href="{{ route('equipment.create') }}" class="btn btn-primary">+ পণ্য</a>
</div>

{{-- Main category chips --}}
<div class="chips">
    <a href="{{ route('equipment.index', array_filter(['district' => $filters['district'] ?? null, 'q' => $filters['q'] ?? null])) }}"
       class="chip {{ empty($filters['category']) ? 'active' : '' }}">সব</a>
    @foreach($mains as $cat)
        <a href="{{ route('equipment.index', array_merge(array_filter(['district' => $filters['district'] ?? null, 'q' => $filters['q'] ?? null]), ['category' => $cat->slug])) }}"
           class="chip {{ ($filters['category'] ?? null) === $cat->slug ? 'active' : '' }}">{{ $cat->icon }} {{ $cat->name }}</a>
    @endforeach
</div>

{{-- Subcategory chips (only when a main category is selected) --}}
@if($activeMain && $activeMain->children->count())
    <div class="chips sub">
        <a href="{{ route('equipment.index', array_merge(array_filter(['district' => $filters['district'] ?? null, 'q' => $filters['q'] ?? null]), ['category' => $activeMain->slug])) }}"
           class="chip {{ empty($filters['sub']) ? 'active' : '' }}">সব {{ $activeMain->name }}</a>
        @foreach($activeMain->children as $sub)
            <a href="{{ route('equipment.index', array_merge(array_filter(['district' => $filters['district'] ?? null, 'q' => $filters['q'] ?? null]), ['category' => $activeMain->slug, 'sub' => $sub->slug])) }}"
               class="chip {{ ($filters['sub'] ?? null) === $sub->slug ? 'active' : '' }}">{{ $sub->name }}</a>
        @endforeach
    </div>
@endif

{{-- Search + district filter --}}
<form method="GET" class="filter-row">
    <input type="hidden" name="category" value="{{ $filters['category'] ?? '' }}">
    <input type="hidden" name="sub" value="{{ $filters['sub'] ?? '' }}">
    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="🔍 পণ্য / ব্র্যান্ড খুঁজুন...">
    <select name="district">
        <option value="">সব জেলা</option>
        @foreach($districts as $d)
            <option value="{{ $d->bn_name }}" {{ ($filters['district'] ?? null) === $d->bn_name ? 'selected' : '' }}>{{ $d->bn_name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary" style="flex: 0 0 auto;">খুঁজুন</button>
</form>

@forelse($products as $product)
    <div class="prod-card">
        @if($product->featured)<span class="featured-tag">⭐ ফিচার্ড</span>@endif

        <a href="{{ route('equipment.show', $product) }}">
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="prod-image" loading="lazy">
            @else
                <div class="prod-image-ph">{{ $product->category->icon ?? '🚜' }}</div>
            @endif
        </a>

        <div class="prod-body">
            <a href="{{ route('equipment.show', $product) }}"><h3 class="prod-title">{{ $product->name }}</h3></a>
            <div class="prod-meta">
                <span class="badge badge-green">৳{{ number_format($product->price, 0) }}{{ $product->unit ? ' / ' . $product->unit : '' }}</span>
                @if($product->brand)<span class="badge badge-amber">🏭 {{ $product->brand }}</span>@endif
                <span class="badge badge-sky">📍 {{ $product->location }}{{ $product->upazila ? ', ' . $product->upazila : '' }}</span>
                @if($product->condition_label)<span class="badge" style="background: var(--brown-50); color: #8a5a2b;">🏷️ {{ $product->condition_label }}</span>@endif
                @if($product->category)<span class="badge" style="background: var(--green-50); color: var(--green-600);">{{ $product->category->name }}</span>@endif
            </div>
            <div class="prod-actions">
                <a href="{{ route('equipment.show', $product) }}" class="btn-view">👁️ বিস্তারিত</a>
                <a href="tel:{{ $product->mobile }}" class="btn-call">📞 কল</a>
                <a href="https://wa.me/88{{ $product->whatsapp_number }}" target="_blank" class="btn-wa">💬 WhatsApp</a>
            </div>
        </div>
    </div>
@empty
    <div class="card" style="text-align: center; color: var(--text-muted);">
        <p style="font-size: 2rem;">🚜</p>
        <p>কোনো পণ্য পাওয়া যায়নি</p>
        <a href="{{ route('equipment.create') }}" class="btn btn-primary" style="margin-top: .75rem;">প্রথম পণ্য যোগ করুন</a>
    </div>
@endforelse

{{ $products->links() }}
@endsection
