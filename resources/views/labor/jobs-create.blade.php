@extends('layouts.app')

@section('title', 'কাজ পোস্ট করুন')

@section('content')
<h2 class="page-title">📋 শ্রমিকের কাজ পোস্ট</h2>

<div class="card">
    <form method="POST" action="{{ route('labor.jobs.store') }}">
        @csrf
        <div class="form-group">
            <label>কাজের ধরন *</label>
            <select name="job_type" required>
                <option value="">নির্বাচন করুন</option>
                @foreach($skills as $skill)
                    <option value="{{ $skill }}" @selected(old('job_type') === $skill)>{{ $skill }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>লোকেশন *</label>
            <input type="text" name="location" value="{{ old('location', auth()->user()->district) }}" placeholder="জেলা / উপজেলা / গ্রাম" required>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>প্রয়োজনীয় শ্রমিক *</label>
                <input type="number" name="worker_needed" value="{{ old('worker_needed', 1) }}" min="1" placeholder="৫" required>
            </div>
            <div class="form-group">
                <label>মজুরি (টাকা) *</label>
                <input type="number" name="wage" value="{{ old('wage') }}" placeholder="৭০০" required>
            </div>
        </div>

        <div class="form-group">
            <label>কাজের সময়কাল</label>
            <input type="text" name="duration" value="{{ old('duration') }}" placeholder="যেমন: ৩ দিন / ১ সপ্তাহ">
        </div>

        <div class="form-group">
            <label>যোগাযোগ নম্বর *</label>
            <input type="tel" name="contact_number" value="{{ old('contact_number', auth()->user()->mobile) }}" placeholder="01XXXXXXXXX" maxlength="11" required>
        </div>

        <div style="display: flex; gap: .5rem;">
            <a href="{{ route('labor.index') }}" class="btn btn-secondary" style="flex: 1; justify-content: center;">বাতিল</a>
            <button type="submit" class="btn btn-primary" style="flex: 2; justify-content: center;">পোস্ট প্রকাশ করুন</button>
        </div>
    </form>
</div>
@endsection
