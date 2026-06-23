@php
    /** Shared field set for create ($t === null) and edit ($t = VoiceTemplate). */
    $exampleDtmf = '[
  {
    "key": "1",
    "option_type": "voice",
    "texts": ["আপনার অনুরোধ গ্রহণ করা হয়েছে।"]
  },
  {
    "key": "2",
    "option_type": "voice",
    "texts": ["ধন্যবাদ।"]
  }
]';
    $dtmfText = $t
        ? json_encode($t->dtmf_options ?: [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        : $exampleDtmf;
@endphp

@if($t)
    {{-- Feature type is fixed once created --}}
    <div class="form-group">
        <label>ফিচার টাইপ</label>
        <input type="text" value="{{ \App\Models\VoiceTemplate::TYPES[$t->type] ?? $t->type }}" disabled>
    </div>
@else
    <div class="form-group">
        <label>ফিচার টাইপ *</label>
        <select name="type" required>
            <option value="">নির্বাচন করুন</option>
            @foreach(\App\Models\VoiceTemplate::TYPES as $val => $lbl)
                <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>
    </div>
@endif

<div class="form-group">
    <label>টেমপ্লেটের নাম *</label>
    <input type="text" name="title" value="{{ $t ? $t->title : old('title') }}" placeholder="যেমন: আবহাওয়া সতর্কতা (ডিফল্ট)" required>
</div>

<div class="form-group">
    <label>শুরুর বার্তা (start text)</label>
    <input type="text" name="start_text" value="{{ $t ? $t->start_text : old('start_text') }}" placeholder="হ্যালো @{{name}}।">
</div>

<div class="form-group">
    <label>মূল প্রশ্ন / বার্তা (question text) *</label>
    <textarea name="question_text" required placeholder="আপনার এলাকায় আবহাওয়া সতর্কতা রয়েছে। বিস্তারিত শুনতে ১ চাপুন।">{{ $t ? $t->question_text : old('question_text') }}</textarea>
</div>

<div class="form-group">
    <label>শেষ বার্তা (end text)</label>
    <input type="text" name="end_text" value="{{ $t ? $t->end_text : old('end_text') }}" placeholder="ধন্যবাদ। কৃষি-বন্ধুর সাথে থাকুন।">
</div>

<div class="grid-2" style="gap: .5rem;">
    <div class="form-group">
        <label>ভয়েস টাইপ</label>
        <select name="voice_type">
            <option value="" {{ $t && $t->voice_type ? '' : 'selected' }}>ডিফল্ট (সেটিংস অনুযায়ী)</option>
            <option value="female" {{ $t && $t->voice_type === 'female' ? 'selected' : '' }}>মহিলা (female)</option>
            <option value="male" {{ $t && $t->voice_type === 'male' ? 'selected' : '' }}>পুরুষ (male)</option>
        </select>
    </div>
    <div class="form-group">
        <label>ভাষা কোড</label>
        <input type="text" name="language_code" value="{{ $t ? $t->language_code : old('language_code') }}" placeholder="bn (খালি = ডিফল্ট)">
    </div>
</div>

<div class="form-group">
    <label>DTMF অপশন (JSON)</label>
    <textarea name="dtmf_options" rows="8" class="json-area">{{ $dtmfText }}</textarea>
    <div class="muted-note">কীপ্যাড অপশন — প্রতিটি <code>key</code>, <code>option_type</code> ("voice"), ও <code>texts</code> অ্যারে। উপরের উদাহরণ অনুসরণ করুন।</div>
</div>

<div class="form-group">
    <label>স্ট্যাটাস</label>
    <select name="status">
        <option value="1" {{ ($t ? $t->status : true) ? 'selected' : '' }}>সক্রিয়</option>
        <option value="0" {{ ($t && ! $t->status) ? 'selected' : '' }}>নিষ্ক্রিয়</option>
    </select>
    <div class="muted-note">একই ফিচারের একাধিক টেমপ্লেট থাকলে — সক্রিয় করলে বাকিগুলো স্বয়ংক্রিয়ভাবে নিষ্ক্রিয় হবে।</div>
</div>
