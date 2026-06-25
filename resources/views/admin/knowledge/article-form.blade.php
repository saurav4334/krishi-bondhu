@extends('layouts.app')

@section('title', $article ? 'আর্টিকেল সম্পাদনা' : 'নতুন আর্টিকেল')

@section('content')
<a href="{{ route('admin.knowledge.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← জ্ঞানভান্ডার</a>
<h2 class="page-title">📝 {{ $article ? 'আর্টিকেল সম্পাদনা' : 'নতুন আর্টিকেল' }}</h2>

<div class="card">
    <form method="POST" action="{{ $article ? route('admin.knowledge.articles.update', $article) : route('admin.knowledge.articles.store') }}">
        @csrf
        @if($article) @method('PATCH') @endif

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>ক্যাটাগরি</label>
                <select name="category_id">
                    <option value="">— নির্বাচন —</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ (int) old('category_id', $article->category_id ?? 0) === $c->id ? 'selected' : '' }}>{{ $c->icon }} {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>স্ট্যাটাস</label>
                <select name="status">
                    <option value="active" {{ old('status', $article->status ?? 'active') === 'active' ? 'selected' : '' }}>সক্রিয়</option>
                    <option value="inactive" {{ old('status', $article->status ?? '') === 'inactive' ? 'selected' : '' }}>নিষ্ক্রিয়</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>শিরোনাম *</label>
            <input type="text" name="title" value="{{ old('title', $article->title ?? '') }}" placeholder="সংক্ষিপ্ত শিরোনাম" required>
        </div>

        <div class="form-group">
            <label>প্রশ্ন * <span style="color: var(--text-muted); font-weight: 400;">(কৃষক যেভাবে জিজ্ঞেস করতে পারে)</span></label>
            <input type="text" name="question" value="{{ old('question', $article->question ?? '') }}" placeholder="যেমন: ধানের পাতা হলুদ কেন?" required>
        </div>

        <div class="form-group">
            <label>কীওয়ার্ড <span style="color: var(--text-muted); font-weight: 400;">(সার্চের জন্য, স্পেস দিয়ে আলাদা)</span></label>
            <input type="text" name="keywords" value="{{ old('keywords', $article->keywords ?? '') }}" placeholder="ধান পাতা হলুদ নাইট্রোজেন">
        </div>

        <div class="form-group">
            <label>উত্তর *</label>
            <textarea name="answer" rows="6" required placeholder="বিস্তারিত উত্তর...">{{ old('answer', $article->answer ?? '') }}</textarea>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>উৎসের নাম <span style="color: var(--text-muted); font-weight: 400;">(trusted source)</span></label>
                <input type="text" name="source_name" value="{{ old('source_name', $article->source_name ?? '') }}" placeholder="যেমন: কৃষি তথ্য সার্ভিস (AIS)">
            </div>
            <div class="form-group">
                <label>উৎসের ধরন</label>
                <select name="source_type">
                    @php $st = old('source_type', $article->source_type ?? ''); @endphp
                    <option value="" {{ $st === '' ? 'selected' : '' }}>—</option>
                    <option value="government" {{ $st === 'government' ? 'selected' : '' }}>সরকারি</option>
                    <option value="research" {{ $st === 'research' ? 'selected' : '' }}>গবেষণা</option>
                    <option value="conversational" {{ $st === 'conversational' ? 'selected' : '' }}>কথোপকথন</option>
                    <option value="community" {{ $st === 'community' ? 'selected' : '' }}>কমিউনিটি</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>উৎসের URL</label>
            <input type="url" name="source_url" value="{{ old('source_url', $article->source_url ?? '') }}" placeholder="https://ais.gov.bd/">
        </div>

        <div style="display: flex; gap: .5rem;">
            <a href="{{ route('admin.knowledge.index') }}" class="btn btn-secondary" style="flex: 1; justify-content: center;">বাতিল</a>
            <button type="submit" class="btn btn-primary" style="flex: 2; justify-content: center;">{{ $article ? 'আপডেট করুন' : 'তৈরি করুন' }}</button>
        </div>
    </form>
</div>
@endsection
