@extends('layouts.app')

@section('title', 'শ্রমিক রেজিস্ট্রেশন')

@section('content')
<h2 class="page-title">👷 শ্রমিক রেজিস্ট্রেশন</h2>

<div class="card">
    <form method="POST" action="{{ route('labor.worker.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label>নাম *</label>
            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" placeholder="পূর্ণ নাম" required>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>মোবাইল নম্বর *</label>
                <input type="tel" name="mobile" value="{{ old('mobile', auth()->user()->mobile) }}" placeholder="01XXXXXXXXX" maxlength="11" required>
            </div>
            <div class="form-group">
                <label>দৈনিক মজুরি (টাকা) *</label>
                <input type="number" name="daily_wage" value="{{ old('daily_wage') }}" placeholder="৭০০" required>
            </div>
        </div>

        <div class="form-group">
            <label>কাজের ধরন *</label>
            <select name="skill_type" required>
                <option value="">নির্বাচন করুন</option>
                @foreach($skills as $skill)
                    <option value="{{ $skill }}" @selected(old('skill_type') === $skill)>{{ $skill }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>জেলা *</label>
                <input type="text" name="district" value="{{ old('district', auth()->user()->district) }}" placeholder="জেলা" required>
            </div>
            <div class="form-group">
                <label>ইউনিয়ন / উপজেলা</label>
                <input type="text" name="area" value="{{ old('area') }}" placeholder="ইউনিয়ন / উপজেলা">
            </div>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>অভিজ্ঞতা</label>
                <input type="text" name="experience" value="{{ old('experience') }}" placeholder="যেমন: ৫ বছর">
            </div>
            <div class="form-group">
                <label>স্ট্যাটাস *</label>
                <select name="availability_status" required>
                    <option value="available" @selected(old('availability_status', 'available') === 'available')>মুক্ত (Available)</option>
                    <option value="busy" @selected(old('availability_status') === 'busy')>ব্যস্ত (Busy)</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>প্রোফাইল ছবি (ঐচ্ছিক, সর্বোচ্চ ২MB)</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" style="padding: 6px;">
        </div>

        <div style="display: flex; gap: .5rem;">
            <a href="{{ route('labor.index') }}" class="btn btn-secondary" style="flex: 1; justify-content: center;">বাতিল</a>
            <button type="submit" class="btn btn-primary" style="flex: 2; justify-content: center;">রেজিস্টার করুন</button>
        </div>
    </form>
</div>
@endsection
