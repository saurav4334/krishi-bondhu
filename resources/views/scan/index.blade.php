@extends('layouts.app')

@section('title', 'রোগ স্ক্যান')

@section('content')
<style>
    #scan-drop { border: 2px dashed var(--green-200); border-radius: var(--radius-lg); padding: 2rem; text-align: center; cursor: pointer; transition: all .2s; background: var(--green-50); position: relative; }
    #scan-drop:hover { border-color: var(--green-400); background: var(--green-100); }
    #scan-drop input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    #scan-drop .icon { font-size: 3rem; margin-bottom: .5rem; }
    #scan-drop p { color: var(--text-secondary); font-size: 14px; }
    #scan-preview { display: none; margin: 1rem 0; }
    #scan-preview img { width: 100%; border-radius: var(--radius); max-height: 260px; object-fit: cover; }
    .result-card { background: var(--green-50); border: 2px solid var(--green-200); border-radius: var(--radius-lg); padding: 1.25rem; margin-top: 1rem; }
    .result-card .disease-name { font-size: 1.2rem; color: var(--green-700); font-weight: 600; margin-bottom: .75rem; }
    .result-section { margin-bottom: .75rem; }
    .result-section label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; }
    .result-section p { font-size: 14px; color: var(--text-primary); margin-top: 3px; }
    .confidence-bar { background: var(--border); border-radius: 999px; height: 8px; margin-top: 6px; overflow: hidden; }
    .confidence-fill { height: 100%; background: var(--green-400); border-radius: 999px; }
    .scan-hist-item { display: flex; gap: .75rem; align-items: center; padding: .75rem 0; border-bottom: 1px solid var(--border); }
    .scan-hist-item:last-child { border: none; }
    .scan-thumb { width: 48px; height: 48px; border-radius: 8px; background: var(--green-50); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; overflow: hidden; }
    .scan-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .scan-hist-item .info h4 { font-size: 14px; font-weight: 600; font-family: 'Hind Siliguri', sans-serif; }
    .scan-hist-item .info p { font-size: 12px; color: var(--text-muted); }
</style>

<h2 class="page-title">🔬 ফসলের রোগ শনাক্তকরণ</h2>

<div class="card">
    <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 1rem;">
        ফসলের রোগাক্রান্ত অংশের ছবি আপলোড করুন। AI বিশ্লেষণ করে রোগের নাম ও চিকিৎসা জানাবে।
    </p>

    <form method="POST" action="{{ route('scan.store') }}" enctype="multipart/form-data" id="scan-form">
        @csrf
        <label id="scan-drop" for="scan-file">
            <input type="file" name="image" id="scan-file" accept="image/jpeg,image/png,image/webp" required>
            <div class="icon">📷</div>
            <p><strong>ছবি আপলোড করুন</strong></p>
            <p style="margin-top: .25rem;">JPG, PNG, WebP (সর্বোচ্চ ২MB)</p>
        </label>

        <div id="scan-preview">
            <img id="scan-img" src="" alt="preview">
        </div>

        <button type="submit" class="btn btn-primary btn-block" id="scan-submit" style="margin-top: 1rem; display: none;">
            AI দিয়ে বিশ্লেষণ করুন 🔬
        </button>
    </form>

    @if(session('scan_result'))
        @php $r = session('scan_result'); @endphp
        <div class="result-card">
            <div class="disease-name">🦠 {{ $r->disease_name }}</div>
            <div class="result-section">
                <label>নির্ভরযোগ্যতা</label>
                <div style="display: flex; align-items: center; gap: .5rem;">
                    <div class="confidence-bar" style="flex: 1;">
                        <div class="confidence-fill" style="width: {{ $r->confidence_score }}%;"></div>
                    </div>
                    <span style="font-weight: 600; color: var(--green-500); font-size: 14px;">{{ $r->confidence_score }}%</span>
                </div>
            </div>
            <div class="result-section"><label>লক্ষণ</label><p>{{ $r->symptoms }}</p></div>
            <div class="result-section"><label>চিকিৎসা</label><p style="color: var(--green-700);">{{ $r->treatment }}</p></div>
            <div class="result-section"><label>প্রতিরোধ</label><p>{{ $r->prevention }}</p></div>
            <div style="display: flex; gap: .5rem; margin-top: .75rem;">
                <a href="{{ route('scan.index') }}" class="btn btn-primary" style="flex: 1; justify-content: center;">নতুন স্ক্যান</a>
                <a href="{{ route('experts.index') }}" class="btn btn-secondary">বিশেষজ্ঞ দেখুন</a>
            </div>
        </div>
    @endif
</div>

<div class="section-title">📋 স্ক্যান ইতিহাস</div>
<div class="card card-sm">
    @forelse($scans as $scan)
        <div class="scan-hist-item">
            <div class="scan-thumb">
                @if($scan->image)
                    <img src="{{ asset('storage/' . $scan->image) }}" alt="">
                @else
                    🌿
                @endif
            </div>
            <div class="info" style="flex: 1;">
                <h4>{{ $scan->disease_name }}</h4>
                <p>নির্ভরযোগ্যতা: {{ $scan->confidence_score }}% · {{ $scan->created_at->diffForHumans() }}</p>
            </div>
            <span class="badge badge-green">সম্পন্ন</span>
        </div>
    @empty
        <p style="color: var(--text-muted); font-size: 14px; text-align: center; padding: 1rem;">কোনো স্ক্যান নেই</p>
    @endforelse
</div>

@push('scripts')
<script>
    document.getElementById('scan-file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('scan-img').src = ev.target.result;
            document.getElementById('scan-preview').style.display = 'block';
            document.getElementById('scan-submit').style.display = 'flex';
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('scan-form').addEventListener('submit', function() {
        const btn = document.getElementById('scan-submit');
        btn.innerText = 'বিশ্লেষণ চলছে...';
        btn.disabled = true;
    });
</script>
@endpush
@endsection
