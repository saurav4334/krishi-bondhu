@extends('layouts.app')

@section('title', 'জ্ঞানভান্ডার ব্যবস্থাপনা')

@section('content')
<style>
    .admin-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .admin-table th { text-align: left; padding: .5rem .6rem; background: var(--green-50); color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
    .admin-table td { padding: .5rem .6rem; border-bottom: 1px solid var(--border); vertical-align: top; }
    .card h3 { font-size: 1rem; color: var(--green-700); margin-bottom: .75rem; }
    .mini-stat { display: grid; grid-template-columns: repeat(3,1fr); gap: .5rem; margin-bottom: 1rem; }
    .mini-stat div { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: .6rem; text-align: center; }
    .mini-stat .n { font-size: 1.3rem; font-weight: 700; color: var(--green-500); }
    .mini-stat .l { font-size: 11px; color: var(--text-muted); }
    .inline-actions form { display: inline; } .inline-actions button { background: none; font-size: 15px; padding: 2px 4px; }
    .ua details { border: 1px solid var(--border); border-radius: var(--radius-sm); margin-bottom: .5rem; }
    .ua summary { padding: .6rem .8rem; cursor: pointer; font-size: 13px; }
</style>

<a href="{{ route('admin.index') }}" class="btn btn-secondary" style="margin-bottom: 1rem;">← অ্যাডমিন</a>
<h2 class="page-title">📚 কৃষি জ্ঞানভান্ডার ব্যবস্থাপনা</h2>

{{-- Analytics --}}
<div class="mini-stat">
    <div><div class="n">{{ $analytics['total'] }}</div><div class="l">মোট প্রশ্ন</div></div>
    <div><div class="n">{{ $analytics['kb'] }}</div><div class="l">KB উত্তর</div></div>
    <div><div class="n">{{ $analytics['ai'] }}</div><div class="l">AI উত্তর</div></div>
</div>
<div class="mini-stat">
    <div><div class="n">{{ $analytics['kb_rate'] }}%</div><div class="l">KB হার</div></div>
    <div><div class="n">{{ $analytics['unanswered'] }}</div><div class="l">অমীমাংসিত</div></div>
    <div><div class="n">{{ $analytics['articles'] }}</div><div class="l">আর্টিকেল</div></div>
</div>

{{-- Top categories + most viewed --}}
<div class="card">
    <h3>📊 অ্যানালিটিক্স</h3>
    <div style="font-size: 13px; margin-bottom: .5rem;"><strong>শীর্ষ ক্যাটাগরি (ভিউ):</strong>
        @forelse($topCategories as $c)<span class="badge badge-green" style="margin: 2px;">{{ $c->icon }} {{ $c->name }} ({{ $c->views ?? 0 }})</span>@empty — @endforelse
    </div>
    <div style="font-size: 13px;"><strong>সর্বাধিক দেখা আর্টিকেল:</strong>
        <ul style="margin: .35rem 0 0 1rem;">
            @forelse($mostViewed as $a)<li>{{ $a->question }} <span style="color: var(--text-muted);">— 👁️ {{ $a->views_count }}</span></li>@empty <li>—</li> @endforelse
        </ul>
    </div>
</div>

{{-- Categories --}}
<div class="card">
    <h3>📂 ক্যাটাগরি</h3>
    <form method="POST" action="{{ route('admin.knowledge.categories.store') }}" style="display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: .75rem;">
        @csrf
        <input type="text" name="name" placeholder="ক্যাটাগরির নাম" required style="flex: 2 1 130px; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
        <input type="text" name="icon" placeholder="🌾" maxlength="16" style="flex: 0 1 70px; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
        <input type="number" name="sort_order" placeholder="ক্রম" min="0" style="flex: 0 1 70px; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
        <button class="btn btn-primary">+ যোগ</button>
    </form>
    <table class="admin-table">
        <thead><tr><th>ক্যাটাগরি</th><th>আর্টিকেল</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr></thead>
        <tbody>
            @foreach($categories as $cat)
                <tr>
                    <td>{{ $cat->icon }} {{ $cat->name }}</td>
                    <td>{{ $cat->articles_count }}</td>
                    <td>@if($cat->status === 'active')<span class="badge badge-green">সক্রিয়</span>@else<span class="badge badge-red">নিষ্ক্রিয়</span>@endif</td>
                    <td class="inline-actions" style="white-space: nowrap;">
                        <button type="button" onclick="editKbCat({{ $cat->id }}, @js($cat->name))" title="সম্পাদনা">✏️</button>
                        <form method="POST" action="{{ route('admin.knowledge.categories.toggle', $cat) }}">@csrf @method('PATCH')<button title="সক্রিয়/নিষ্ক্রিয়">{{ $cat->status === 'active' ? '🚫' : '✅' }}</button></form>
                        <form method="POST" action="{{ route('admin.knowledge.categories.delete', $cat) }}" onsubmit="return confirm('ক্যাটাগরি মুছবেন?')">@csrf @method('DELETE')<button title="মুছুন">🗑️</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Articles --}}
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: .5rem;">
        <h3 style="margin: 0;">📝 আর্টিকেল ({{ $analytics['articles'] }})</h3>
        <a href="{{ route('admin.knowledge.articles.create') }}" class="btn btn-primary" style="padding: 6px 12px;">+ নতুন আর্টিকেল</a>
    </div>
    <form method="GET" style="display: flex; gap: .5rem; margin-bottom: .75rem;">
        <input type="text" name="q" value="{{ $search }}" placeholder="🔍 প্রশ্ন খুঁজুন..." style="flex: 1; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);">
        <button class="btn btn-secondary">খুঁজুন</button>
    </form>
    <table class="admin-table">
        <thead><tr><th>প্রশ্ন</th><th>ক্যাটাগরি</th><th>👁️</th><th>অ্যাকশন</th></tr></thead>
        <tbody>
            @forelse($articles as $a)
                <tr>
                    <td>{{ \Illuminate\Support\Str::limit($a->question, 70) }}</td>
                    <td>{{ $a->category->name ?? '—' }}</td>
                    <td>{{ $a->views_count }}</td>
                    <td class="inline-actions" style="white-space: nowrap;">
                        <a href="{{ route('admin.knowledge.articles.edit', $a) }}" title="এডিট">✏️</a>
                        <form method="POST" action="{{ route('admin.knowledge.articles.delete', $a) }}" onsubmit="return confirm('মুছবেন?')">@csrf @method('DELETE')<button title="মুছুন">🗑️</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1rem;">কোনো আর্টিকেল নেই</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top: .75rem;">{{ $articles->links() }}</div>
</div>

{{-- Unanswered questions --}}
<div class="card ua">
    <h3>❓ অমীমাংসিত প্রশ্ন ({{ $analytics['unanswered'] }})</h3>
    @forelse($unanswered as $u)
        <details>
            <summary>{{ $u->question }} <span style="color: var(--text-muted); font-size: 11px;">— {{ $u->user->name ?? 'গেস্ট' }}{{ $u->district ? ', '.$u->district : '' }} · {{ $u->created_at->diffForHumans() }}</span></summary>
            <div style="padding: .75rem; border-top: 1px solid var(--border);">
                <form method="POST" action="{{ route('admin.knowledge.unanswered.convert', $u) }}">
                    @csrf
                    <div class="grid-2" style="gap: .5rem;">
                        <div class="form-group">
                            <label>ক্যাটাগরি</label>
                            <select name="category_id"><option value="">— নির্বাচন —</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
                        </div>
                        <div class="form-group"><label>কীওয়ার্ড</label><input type="text" name="keywords" placeholder="স্পেস দিয়ে আলাদা"></div>
                    </div>
                    <div class="form-group"><label>উত্তর *</label><textarea name="answer" required placeholder="এই প্রশ্নের উত্তর..."></textarea></div>
                    <div style="display: flex; gap: .5rem;">
                        <button type="submit" class="btn btn-primary">➕ জ্ঞানভান্ডারে যুক্ত করুন</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.knowledge.unanswered.delete', $u) }}" onsubmit="return confirm('মুছবেন?')" style="margin-top: .5rem;">@csrf @method('DELETE')<button class="btn btn-danger" style="padding: 5px 12px;">🗑️ মুছুন</button></form>
            </div>
        </details>
    @empty
        <p style="text-align: center; color: var(--text-muted); padding: .5rem;">কোনো অমীমাংসিত প্রশ্ন নেই 🎉</p>
    @endforelse
</div>

{{-- shared rename form --}}
<form method="POST" id="edit-kbcat-form" action="">@csrf @method('PATCH')<input type="hidden" name="name" id="edit-kbcat-name"></form>
@push('scripts')
<script>
    function editKbCat(id, current) {
        var name = prompt('নতুন ক্যাটাগরির নাম:', current);
        if (name === null || name.trim() === '') return;
        var f = document.getElementById('edit-kbcat-form');
        f.action = '{{ url('admin/knowledge/categories') }}/' + id;
        document.getElementById('edit-kbcat-name').value = name.trim();
        f.submit();
    }
</script>
@endpush
@endsection
