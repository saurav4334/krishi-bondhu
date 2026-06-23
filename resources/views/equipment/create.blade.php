@extends('layouts.app')

@section('title', 'পণ্য যোগ করুন')

@section('content')
<h2 class="page-title">🚜 নতুন পণ্য — কৃষি সরঞ্জাম</h2>

<div class="card">
    <form method="POST" action="{{ route('equipment.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>মূল ক্যাটাগরি *</label>
                <select id="eq-main" required>
                    <option value="">নির্বাচন করুন</option>
                    @foreach($mains as $main)
                        <option value="{{ $main->id }}">{{ $main->icon }} {{ $main->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>উপ-ক্যাটাগরি *</label>
                <select name="category_id" id="eq-sub" required>
                    <option value="">আগে ক্যাটাগরি বাছুন</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>পণ্যের নাম *</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="যেমন: পাওয়ার টিলার / ইউরিয়া সার" required>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>ব্র্যান্ড</label>
                <input type="text" name="brand" value="{{ old('brand') }}" placeholder="যেমন: Yanmar / ACI">
            </div>
            <div class="form-group">
                <label>মডেল</label>
                <input type="text" name="model" value="{{ old('model') }}" placeholder="যেমন: VST-130">
            </div>
        </div>

        <div class="grid-3" style="gap: .5rem;">
            <div class="form-group">
                <label>দাম (৳) *</label>
                <input type="number" name="price" value="{{ old('price') }}" placeholder="১০০০" step="0.01" required>
            </div>
            <div class="form-group">
                <label>স্টক</label>
                <input type="number" name="stock_quantity" value="{{ old('stock_quantity') }}" placeholder="১০" min="0">
            </div>
            <div class="form-group">
                <label>একক</label>
                <input type="text" name="unit" value="{{ old('unit') }}" placeholder="টি / কেজি / ব্যাগ">
            </div>
        </div>

        <div class="form-group">
            <label>অবস্থা <span style="color: var(--text-muted); font-weight: 400;">(যন্ত্রপাতির জন্য)</span></label>
            <select name="condition">
                <option value="">প্রযোজ্য নয়</option>
                @foreach(\App\Models\EquipmentProduct::CONDITIONS as $val => $lbl)
                    <option value="{{ $val }}" {{ old('condition') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>জেলা *</label>
                <select name="location" id="eq-district" required>
                    <option value="">নির্বাচন করুন</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->bn_name }}" data-id="{{ $d->id }}" {{ old('location', auth()->user()->district) === $d->bn_name ? 'selected' : '' }}>{{ $d->bn_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>উপজেলা</label>
                <select name="upazila" id="eq-upazila">
                    <option value="">আগে জেলা নির্বাচন করুন</option>
                </select>
            </div>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>মোবাইল নম্বর *</label>
                <input type="tel" name="mobile" value="{{ old('mobile', auth()->user()->mobile) }}" placeholder="01XXXXXXXXX" maxlength="11" required>
            </div>
            <div class="form-group">
                <label>WhatsApp নম্বর</label>
                <input type="tel" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="01XXXXXXXXX (ঐচ্ছিক)" maxlength="11">
            </div>
        </div>

        <div class="form-group">
            <label>বিবরণ</label>
            <textarea name="description" placeholder="পণ্যের গুণমান, বৈশিষ্ট্য, বিক্রির শর্ত...">{{ old('description') }}</textarea>
        </div>

        <div class="form-group">
            <label>ছবি * (সর্বোচ্চ ৬টি, প্রতিটি ১০MB পর্যন্ত)</label>
            <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required style="padding: 6px;">
        </div>

        <div style="display: flex; gap: .5rem;">
            <a href="{{ route('equipment.index') }}" class="btn btn-secondary" style="flex: 1; justify-content: center;">বাতিল</a>
            <button type="submit" class="btn btn-primary" style="flex: 2; justify-content: center;">পণ্য জমা দিন</button>
        </div>
        <p style="font-size: 12px; color: var(--text-muted); margin-top: .6rem; text-align: center;">অ্যাডমিন অনুমোদনের পর পণ্যটি প্রকাশিত হবে।</p>
    </form>
</div>

@push('scripts')
<script>
    // --- Category cascade (main → subcategory) ---
    const EQ_CATS = @json($mains->mapWithKeys(fn ($m) => [$m->id => $m->children->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])]));
    const EQ_OLD_SUB = @json(old('category_id'));
    const eqMain = document.getElementById('eq-main');
    const eqSub = document.getElementById('eq-sub');

    function eqLoadSubs(selected) {
        const list = EQ_CATS[eqMain.value] || [];
        eqSub.innerHTML = '<option value="">' + (eqMain.value ? 'উপ-ক্যাটাগরি নির্বাচন করুন' : 'আগে ক্যাটাগরি বাছুন') + '</option>';
        list.forEach(c => {
            const o = document.createElement('option');
            o.value = c.id;
            o.textContent = c.name;
            if (selected && String(selected) === String(c.id)) o.selected = true;
            eqSub.appendChild(o);
        });
    }
    eqMain.addEventListener('change', () => eqLoadSubs());

    // Restore old selection (validation error) by finding the parent of the old sub id
    if (EQ_OLD_SUB) {
        for (const [mid, subs] of Object.entries(EQ_CATS)) {
            if (subs.some(c => String(c.id) === String(EQ_OLD_SUB))) { eqMain.value = mid; break; }
        }
        eqLoadSubs(EQ_OLD_SUB);
    }

    // --- District cascade (district → upazila) ---
    const EQ_UPAZILAS = @json($upazilas);
    const EQ_OLD_UPAZILA = @json(old('upazila'));
    const eqDistrict = document.getElementById('eq-district');
    const eqUpazila = document.getElementById('eq-upazila');

    function eqLoadUpazilas(selected) {
        const opt = eqDistrict.selectedOptions[0];
        const id = opt ? opt.dataset.id : null;
        const list = id ? EQ_UPAZILAS.filter(u => String(u.district_id) === String(id)) : [];
        eqUpazila.innerHTML = '<option value="">' + (id ? 'উপজেলা নির্বাচন করুন' : 'আগে জেলা নির্বাচন করুন') + '</option>';
        list.forEach(u => {
            const o = document.createElement('option');
            o.value = u.bn_name;
            o.textContent = u.bn_name;
            if (selected && selected === u.bn_name) o.selected = true;
            eqUpazila.appendChild(o);
        });
    }
    eqDistrict.addEventListener('change', () => eqLoadUpazilas());
    if (eqDistrict.value) eqLoadUpazilas(EQ_OLD_UPAZILA);
</script>
@endpush
@endsection
