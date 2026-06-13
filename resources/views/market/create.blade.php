@extends('layouts.app')

@section('title', 'পোস্ট তৈরি')

@section('content')
<h2 class="page-title">🌾 নতুন বিজ্ঞাপন</h2>

<div class="card">
    <form method="POST" action="{{ route('market.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>ক্যাটাগরি *</label>
            <select name="category_id" required>
                <option value="">নির্বাচন করুন</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->icon }} {{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>পণ্যের নাম *</label>
            <input type="text" name="crop_name" value="{{ old('crop_name') }}" placeholder="যেমন: ব্রি ধান২৮ বীজ" required>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>পরিমাণ *</label>
                <input type="text" name="quantity" value="{{ old('quantity') }}" placeholder="৫০ কেজি" required>
            </div>
            <div class="form-group">
                <label>দাম (টাকা) *</label>
                <input type="number" name="price" value="{{ old('price') }}" placeholder="১০০০" step="0.01" required>
            </div>
        </div>

        <div class="form-group">
            <label>জেলা *</label>
            <select name="location" required>
                <option value="">নির্বাচন করুন</option>
                @foreach($districts as $d)
                    <option value="{{ $d->bn_name }}" {{ old('location', auth()->user()->district) === $d->bn_name ? 'selected' : '' }}>{{ $d->bn_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>মোবাইল নম্বর *</label>
            <input type="tel" name="mobile" value="{{ old('mobile', auth()->user()->mobile) }}" placeholder="01XXXXXXXXX" maxlength="11" required>
        </div>

        <div class="form-group">
            <label>বিবরণ</label>
            <textarea name="description" placeholder="পণ্যের গুণমান, বিক্রির শর্ত...">{{ old('description') }}</textarea>
        </div>

        <div class="form-group">
            <label>ছবি * (সর্বোচ্চ ৫টি, প্রতিটি ১০MB পর্যন্ত)</label>
            <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required style="padding: 6px;">
        </div>

        <div style="display: flex; gap: .5rem;">
            <a href="{{ route('market.index') }}" class="btn btn-secondary" style="flex: 1; justify-content: center;">বাতিল</a>
            <button type="submit" class="btn btn-primary" style="flex: 2; justify-content: center;">পোস্ট জমা দিন</button>
        </div>
        <p style="font-size: 12px; color: var(--text-muted); margin-top: .6rem; text-align: center;">অ্যাডমিন অনুমোদনের পর বিজ্ঞাপন প্রকাশিত হবে।</p>
    </form>
</div>
@endsection
