@extends('layouts.app')

@section('title', 'বাজার দর')

@section('content')
<style>
    .price-row { display: flex; align-items: center; justify-content: space-between; padding: .85rem 0; border-bottom: 1px solid var(--border); }
    .price-row:last-child { border: none; }
    .price-row .crop { display: flex; align-items: center; gap: .65rem; }
    .price-row .crop-emoji { font-size: 1.4rem; }
    .price-row .crop-name { font-weight: 500; font-size: 15px; }
    .price-row .crop-unit { font-size: 12px; color: var(--text-muted); }
    .price-row .price-val { font-size: 1.1rem; font-weight: 700; color: var(--green-600); }
    .price-row .price-unit { font-size: 11px; color: var(--text-muted); }
    .price-search { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); background: #fff; font-size: 15px; margin-bottom: .75rem; }
    .price-search:focus { border-color: var(--green-400); outline: none; }
</style>

<h2 class="page-title">💰 আজকের বাজার দর</h2>

<form method="GET" action="{{ route('prices.index') }}">
    <input type="search" name="search" value="{{ $search }}" class="price-search" placeholder="🔍 ফসলের নাম লিখুন... (Enter চাপুন)">
</form>

<div class="card card-sm">
    @forelse($prices as $price)
        @php
            $emojis = ['ধান' => '🌾', 'গম' => '🌾', 'আলু' => '🥔', 'পাট' => '🌿', 'পেঁয়াজ' => '🧅', 'টমেটো' => '🍅', 'কলা' => '🍌', 'ইলিশ' => '🐟', 'বেগুন' => '🍆', 'মরিচ' => '🌶️', 'লাউ' => '🥒', 'করলা' => '🥒'];
            $emoji = '🌱';
            foreach ($emojis as $key => $val) {
                if (str_contains($price->crop_name, $key)) { $emoji = $val; break; }
            }
        @endphp
        <div class="price-row">
            <div class="crop">
                <span class="crop-emoji">{{ $emoji }}</span>
                <div>
                    <div class="crop-name">{{ $price->crop_name }}</div>
                    <div class="crop-unit">{{ $price->district }}</div>
                </div>
            </div>
            <div style="text-align: right;">
                <div class="price-val">৳{{ number_format($price->price, 0) }}</div>
                <div class="price-unit">প্রতি {{ $price->unit }}</div>
            </div>
        </div>
    @empty
        <p style="color: var(--text-muted); text-align: center; padding: 1rem;">
            @if($search)
                "{{ $search }}" দিয়ে কিছু পাওয়া যায়নি
            @else
                কোনো ডেটা নেই
            @endif
        </p>
    @endforelse
</div>

<p style="font-size: 12px; color: var(--text-muted); text-align: center; margin-top: .5rem;">
    সর্বশেষ আপডেট: {{ optional($prices->first())->updated_at?->diffForHumans() ?? 'আজকে' }}
</p>
@endsection
