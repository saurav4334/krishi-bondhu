@extends('layouts.app')

@section('title', 'ফসল বিক্রয়')

@section('content')
<h2 class="page-title">🌾 নতুন ফসল বিজ্ঞাপন</h2>

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
            <label>ফসলের নাম *</label>
            <input type="text" name="crop_name" value="{{ old('crop_name') }}" placeholder="যেমন: আমন ধান / মিনিকেট চাল / লাল আলু" required>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>পরিমাণ *</label>
                <input type="text" name="quantity" value="{{ old('quantity') }}" placeholder="১ টি / ৫০ কেজি" required>
            </div>
            <div class="form-group">
                <label>দাম (টাকা) *</label>
                <input type="number" name="price" value="{{ old('price') }}" placeholder="১০০০" step="0.01" required>
            </div>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>জেলা *</label>
                <select name="location" id="mk-district" required>
                    <option value="">নির্বাচন করুন</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->bn_name }}" data-id="{{ $d->id }}" {{ old('location', auth()->user()->district) === $d->bn_name ? 'selected' : '' }}>{{ $d->bn_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>উপজেলা</label>
                <select name="upazila" id="mk-upazila">
                    <option value="">আগে জেলা নির্বাচন করুন</option>
                </select>
            </div>
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

@push('scripts')
<script>
    const MK_UPAZILAS = @json($upazilas);
    const MK_OLD_UPAZILA = @json(old('upazila'));
    const mkDistrict = document.getElementById('mk-district');
    const mkUpazila = document.getElementById('mk-upazila');

    function mkLoadUpazilas(selected) {
        const opt = mkDistrict.selectedOptions[0];
        const id = opt ? opt.dataset.id : null;
        const list = id ? MK_UPAZILAS.filter(u => String(u.district_id) === String(id)) : [];
        mkUpazila.innerHTML = '<option value="">' + (id ? 'উপজেলা নির্বাচন করুন' : 'আগে জেলা নির্বাচন করুন') + '</option>';
        list.forEach(u => {
            const o = document.createElement('option');
            o.value = u.bn_name;
            o.textContent = u.bn_name;
            if (selected && selected === u.bn_name) o.selected = true;
            mkUpazila.appendChild(o);
        });
    }

    mkDistrict.addEventListener('change', () => mkLoadUpazilas());
    // Populate on load if a district is already selected (default user district / old input)
    if (mkDistrict.value) mkLoadUpazilas(MK_OLD_UPAZILA);
</script>
@endpush
@endsection
