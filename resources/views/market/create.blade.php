@extends('layouts.app')

@section('title', 'পোস্ট তৈরি')

@section('content')
<h2 class="page-title">🌾 ফসল বিক্রির পোস্ট</h2>

<div class="card">
    <form method="POST" action="{{ route('market.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label>ফসলের নাম *</label>
            <input type="text" name="crop_name" value="{{ old('crop_name') }}" placeholder="যেমন: আমন ধান" required>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>পরিমাণ *</label>
                <input type="text" name="quantity" value="{{ old('quantity') }}" placeholder="৫০ মণ" required>
            </div>
            <div class="form-group">
                <label>দাম (টাকা) *</label>
                <input type="number" name="price" value="{{ old('price') }}" placeholder="১০০০" required>
            </div>
        </div>

        <div class="form-group">
            <label>অবস্থান *</label>
            <input type="text" name="location" value="{{ old('location') }}" placeholder="জেলা / উপজেলা" required>
        </div>

        <div class="form-group">
            <label>মোবাইল নম্বর *</label>
            <input type="tel" name="mobile" value="{{ old('mobile', auth()->user()->mobile) }}" placeholder="01XXXXXXXXX" maxlength="11" required>
        </div>

        <div class="form-group">
            <label>বিবরণ</label>
            <textarea name="description" placeholder="ফসলের গুণমান, বিক্রির শর্ত...">{{ old('description') }}</textarea>
        </div>

        <div class="form-group">
            <label>ছবি (ঐচ্ছিক, সর্বোচ্চ ১০MB)</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" style="padding: 6px;">
        </div>

        <div style="display: flex; gap: .5rem;">
            <a href="{{ route('market.index') }}" class="btn btn-secondary" style="flex: 1; justify-content: center;">বাতিল</a>
            <button type="submit" class="btn btn-primary" style="flex: 2; justify-content: center;">পোস্ট প্রকাশ করুন</button>
        </div>
    </form>
</div>
@endsection
