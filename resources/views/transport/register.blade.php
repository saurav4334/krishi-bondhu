@extends('layouts.app')

@section('title', 'পরিবহন রেজিস্ট্রেশন')

@section('content')
<h2 class="page-title">🚜 পরিবহন রেজিস্ট্রেশন</h2>

<div class="card">
    <form method="POST" action="{{ route('transport.provider.store') }}">
        @csrf
        <div class="form-group">
            <label>ড্রাইভারের নাম *</label>
            <input type="text" name="driver_name" value="{{ old('driver_name', auth()->user()->name) }}" placeholder="পূর্ণ নাম" required>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>মোবাইল নম্বর *</label>
                <input type="tel" name="mobile" value="{{ old('mobile', auth()->user()->mobile) }}" placeholder="01XXXXXXXXX" maxlength="11" required>
            </div>
            <div class="form-group">
                <label>রেট (টাকা/কি.মি.)</label>
                <input type="number" name="rate_per_km" value="{{ old('rate_per_km') }}" placeholder="৪০" step="0.01">
            </div>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>গাড়ির ধরন *</label>
                <select name="vehicle_type" required>
                    <option value="">নির্বাচন করুন</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v }}" @selected(old('vehicle_type') === $v)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>গাড়ির নম্বর</label>
                <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" placeholder="ঢাকা মেট্রো-১১">
            </div>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>জেলা *</label>
                <input type="text" name="district" value="{{ old('district', auth()->user()->district) }}" placeholder="জেলা" required>
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
            <label>সার্ভিস এরিয়া</label>
            <input type="text" name="service_area" value="{{ old('service_area') }}" placeholder="যেমন: ঢাকা - ময়মনসিংহ">
        </div>

        <div style="display: flex; gap: .5rem;">
            <a href="{{ route('transport.index') }}" class="btn btn-secondary" style="flex: 1; justify-content: center;">বাতিল</a>
            <button type="submit" class="btn btn-primary" style="flex: 2; justify-content: center;">রেজিস্টার করুন</button>
        </div>
    </form>
</div>
@endsection
