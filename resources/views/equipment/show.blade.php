@extends('layouts.app')

@section('title', $product->name)

@section('content')
<style>
    .gallery-main { width: 100%; height: 260px; object-fit: cover; border-radius: var(--radius-lg); background: var(--green-50); display: block; }
    .gallery-main-ph { width: 100%; height: 260px; background: var(--green-50); display: flex; align-items: center; justify-content: center; font-size: 4rem; border-radius: var(--radius-lg); }
    .thumbs { display: flex; gap: .5rem; overflow-x: auto; margin-top: .6rem; padding-bottom: .25rem; }
    .thumbs img { width: 64px; height: 64px; object-fit: cover; border-radius: var(--radius-sm); border: 2px solid var(--border); cursor: pointer; flex-shrink: 0; }
    .thumbs img.active { border-color: var(--green-500); }
    .spec-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: .5rem; }
    .spec-table td { padding: .55rem .25rem; border-bottom: 1px solid var(--border); }
    .spec-table td:first-child { color: var(--text-muted); width: 42%; }
    .spec-table td:last-child { font-weight: 500; color: var(--text-primary); }
    .contact-actions { display: flex; gap: .6rem; margin-top: 1rem; }
    .contact-actions a { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 13px; border-radius: var(--radius-sm); font-size: 15px; font-weight: 600; }
    .c-call { background: var(--green-500); color: #fff; }
    .c-wa { background: #128c43; color: #fff; }
    .crumb { font-size: 12px; color: var(--text-muted); margin-bottom: .5rem; }
</style>

<a href="{{ route('equipment.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← কৃষি সরঞ্জাম</a>

@if(! $product->approved)
    <div class="alert" style="background: var(--amber-50); border: 1px solid var(--amber-100); color: #7a5200;">
        ⏳ এই পণ্যটি অ্যাডমিন অনুমোদনের অপেক্ষায় আছে — শুধুমাত্র আপনি দেখতে পাচ্ছেন।
    </div>
@endif

<div class="card">
    {{-- Image gallery --}}
    @if($product->images->count())
        <img id="galleryMain" src="{{ asset('storage/' . ($product->image ?: $product->images->first()->image)) }}" alt="{{ $product->name }}" class="gallery-main">
        @if($product->images->count() > 1)
            <div class="thumbs">
                @foreach($product->images as $img)
                    <img src="{{ asset('storage/' . $img->image) }}" alt="" class="{{ $loop->first ? 'active' : '' }}" onclick="eqSwap(this)">
                @endforeach
            </div>
        @endif
    @else
        <div class="gallery-main-ph">{{ $product->category->icon ?? '🚜' }}</div>
    @endif

    {{-- Category breadcrumb --}}
    @if($product->category)
        <div class="crumb" style="margin-top: 1rem;">
            {{ optional($product->category->parent)->name ? $product->category->parent->name . ' › ' : '' }}{{ $product->category->name }}
        </div>
    @endif

    <h2 class="page-title" style="margin: 0 0 .5rem; border: none; padding: 0;">{{ $product->name }}</h2>

    <div style="font-size: 1.5rem; font-weight: 700; color: var(--green-600);">
        ৳{{ number_format($product->price, 0) }}{{ $product->unit ? ' / ' . $product->unit : '' }}
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .6rem;">
        @if($product->featured)<span class="badge badge-amber">⭐ ফিচার্ড</span>@endif
        @if($product->condition_label)<span class="badge" style="background: var(--brown-50); color: #8a5a2b;">🏷️ {{ $product->condition_label }}</span>@endif
        <span class="badge badge-sky">📍 {{ $product->location }}{{ $product->upazila ? ', ' . $product->upazila : '' }}</span>
    </div>

    {{-- Specifications --}}
    <table class="spec-table">
        @if($product->brand)<tr><td>ব্র্যান্ড</td><td>{{ $product->brand }}</td></tr>@endif
        @if($product->model)<tr><td>মডেল</td><td>{{ $product->model }}</td></tr>@endif
        @if(! is_null($product->stock_quantity))<tr><td>স্টক</td><td>{{ $product->stock_quantity }}{{ $product->unit ? ' ' . $product->unit : '' }}</td></tr>@endif
        @if($product->category)<tr><td>ক্যাটাগরি</td><td>{{ $product->category->name }}</td></tr>@endif
        <tr><td>বিক্রেতা</td><td>{{ $product->user->name ?? 'অজানা' }}</td></tr>
        <tr><td>অবস্থান</td><td>{{ $product->location }}{{ $product->upazila ? ', ' . $product->upazila : '' }}</td></tr>
        <tr><td>প্রকাশিত</td><td>{{ $product->created_at->diffForHumans() }}</td></tr>
    </table>

    @if($product->description)
        <div class="section-title" style="margin-top: 1rem;">📝 বিবরণ</div>
        <p style="font-size: 14px; color: var(--text-secondary); white-space: pre-line;">{{ $product->description }}</p>
    @endif

    {{-- Contact seller --}}
    <div class="contact-actions">
        <a href="tel:{{ $product->mobile }}" class="c-call">📞 কল করুন</a>
        <a href="https://wa.me/88{{ $product->whatsapp_number }}" target="_blank" class="c-wa">💬 WhatsApp</a>
    </div>

    @if(auth()->id() === $product->user_id || auth()->user()->isAdmin())
        <form method="POST" action="{{ route('equipment.destroy', $product) }}" onsubmit="return confirm('এই পণ্য মুছে ফেলবেন?')" style="margin-top: .75rem;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-block">🗑️ পণ্য মুছুন</button>
        </form>
    @endif
</div>

@push('scripts')
<script>
    function eqSwap(el) {
        document.getElementById('galleryMain').src = el.src;
        document.querySelectorAll('.thumbs img').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }
</script>
@endpush
@endsection
