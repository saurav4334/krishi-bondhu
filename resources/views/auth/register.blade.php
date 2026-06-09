@extends('layouts.app')

@section('title', 'নিবন্ধন')

@section('content')
<style>
    .auth-page { max-width: 440px; margin: 0 auto; padding: 2rem 1rem; }
    .auth-hero { text-align: center; padding: 1.5rem 0 1rem; }
    .auth-hero .emoji { font-size: 3rem; display: block; margin-bottom: .5rem; }
    .auth-hero h1 { color: var(--green-600); font-size: 1.6rem; font-family: 'Noto Serif Bengali', serif; }
    .auth-link { text-align: center; margin-top: 1rem; color: var(--text-secondary); font-size: 14px; }
    .auth-link a { color: var(--green-600); font-weight: 500; }
</style>

<div class="auth-page">
    <div class="auth-hero">
        <span class="emoji">🌾</span>
        <h1>নতুন অ্যাকাউন্ট</h1>
        <p style="color: var(--text-secondary); margin-top: .3rem;">কৃষি-বন্ধুতে আপনাকে স্বাগতম</p>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>⚠️ {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-group">
                <label>আপনার নাম</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="পূর্ণ নাম" required autofocus>
            </div>
            <div class="form-group">
                <label>মোবাইল নম্বর</label>
                <input type="tel" name="mobile" value="{{ old('mobile') }}" placeholder="01XXXXXXXXX" maxlength="11" required>
            </div>
            <div class="form-group">
                <label>বিভাগ</label>
                <select name="division" id="sel-division" required>
                    <option value="">বিভাগ নির্বাচন করুন</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div->bn_name }}" data-id="{{ $div->id }}" {{ old('division') === $div->bn_name ? 'selected' : '' }}>{{ $div->bn_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>জেলা</label>
                <select name="district" id="sel-district" required>
                    <option value="">আগে বিভাগ নির্বাচন করুন</option>
                </select>
            </div>
            <div class="form-group">
                <label>উপজেলা</label>
                <select name="upazila" id="sel-upazila" required>
                    <option value="">আগে জেলা নির্বাচন করুন</option>
                </select>
            </div>
            <div class="form-group">
                <label>ইউনিয়ন <span style="color: var(--text-muted); font-weight: 400;">(ঐচ্ছিক)</span></label>
                <input type="text" name="union_name" value="{{ old('union_name') }}" placeholder="ইউনিয়নের নাম">
            </div>
            <div class="form-group">
                <label>পাসওয়ার্ড</label>
                <input type="password" name="password" placeholder="কমপক্ষে ৬ অক্ষর" required>
            </div>
            <div class="form-group">
                <label>পাসওয়ার্ড নিশ্চিত করুন</label>
                <input type="password" name="password_confirmation" placeholder="আবার লিখুন" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">নিবন্ধন করুন</button>
        </form>

        <div class="auth-link">
            ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="{{ route('login') }}">লগইন করুন</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const DISTRICTS = @json($districts);
    const UPAZILAS  = @json($upazilas);
    const OLD = { division: @json(old('division')), district: @json(old('district')), upazila: @json(old('upazila')) };

    const selDivision = document.getElementById('sel-division');
    const selDistrict = document.getElementById('sel-district');
    const selUpazila  = document.getElementById('sel-upazila');

    function fill(select, items, placeholder, selectedValue) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        items.forEach(it => {
            const opt = document.createElement('option');
            opt.value = it.bn_name;
            opt.dataset.id = it.id;
            opt.textContent = it.bn_name;
            if (selectedValue && selectedValue === it.bn_name) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function divisionId() {
        const o = selDivision.selectedOptions[0];
        return o ? o.dataset.id : null;
    }
    function districtId() {
        const o = selDistrict.selectedOptions[0];
        return o ? o.dataset.id : null;
    }

    function loadDistricts(selectedValue) {
        const id = divisionId();
        const list = id ? DISTRICTS.filter(d => String(d.division_id) === String(id)) : [];
        fill(selDistrict, list, 'জেলা নির্বাচন করুন', selectedValue);
    }
    function loadUpazilas(selectedValue) {
        const id = districtId();
        const list = id ? UPAZILAS.filter(u => String(u.district_id) === String(id)) : [];
        fill(selUpazila, list, 'উপজেলা নির্বাচন করুন', selectedValue);
    }

    selDivision.addEventListener('change', () => { loadDistricts(); loadUpazilas(); });
    selDistrict.addEventListener('change', () => loadUpazilas());

    // Restore old() selections after a validation error
    if (OLD.division) { loadDistricts(OLD.district); loadUpazilas(OLD.upazila); }
</script>
@endpush
@endsection
