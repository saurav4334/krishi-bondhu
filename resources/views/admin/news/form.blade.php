@extends('layouts.app')

@section('title', $post->exists ? 'সংবাদ এডিট' : 'নতুন সংবাদ')

@section('content')
<a href="{{ route('admin.news.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← সংবাদ তালিকা</a>
<h2 class="page-title">{{ $post->exists ? '✏️ সংবাদ এডিট' : '📰 নতুন সংবাদ' }}</h2>

<div class="card">
    <form method="POST" action="{{ $post->exists ? route('admin.news.update', $post) : route('admin.news.store') }}" enctype="multipart/form-data">
        @csrf
        @if($post->exists) @method('PUT') @endif

        <div class="form-group">
            <label>শিরোনাম *</label>
            <input type="text" name="title" value="{{ old('title', $post->title) }}" required>
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>ক্যাটাগরি *</label>
                <select name="category_id" required>
                    <option value="">নির্বাচন করুন</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) old('category_id', $post->category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>জেলা (ঐচ্ছিক)</label>
                <input type="text" name="district" value="{{ old('district', $post->district) }}" placeholder="খালি = সকল জেলা">
            </div>
        </div>

        <div class="form-group">
            <label>বিবরণ *</label>
            <textarea name="description" rows="8" required style="min-height: 160px;">{{ old('description', $post->description) }}</textarea>
        </div>

        <div class="form-group">
            <label>ফিচার্ড ছবি</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" style="padding: 6px;">
            @if($post->image)<img src="{{ image_url($post->image, 'images/news/default.jpg') }}" alt="" style="margin-top: .5rem; height: 70px; border-radius: 8px;" onerror="this.onerror=null; this.src='{{ asset('images/news/default.jpg') }}';">@endif
        </div>

        <div class="grid-2" style="gap: .5rem;">
            <div class="form-group">
                <label>স্ট্যাটাস</label>
                <select name="status">
                    <option value="published" {{ old('status', $post->status ?: 'published') === 'published' ? 'selected' : '' }}>প্রকাশিত</option>
                    <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>খসড়া</option>
                </select>
            </div>
            <div class="form-group">
                <label>প্রকাশের সময়</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}">
            </div>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 500;">
                <input type="checkbox" name="is_important" value="1" {{ old('is_important', $post->is_important) ? 'checked' : '' }} style="width: auto;">
                গুরুত্বপূর্ণ হিসেবে চিহ্নিত করুন
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-block">সংরক্ষণ করুন</button>
    </form>
</div>
@endsection
