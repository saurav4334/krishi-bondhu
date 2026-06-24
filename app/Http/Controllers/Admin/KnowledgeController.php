<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChatLog;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\UnansweredQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KnowledgeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q'));

        $articles = KnowledgeArticle::with('category')
            ->when($search, fn ($qr) => $qr->where('question', 'like', "%{$search}%")->orWhere('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $total = AiChatLog::count();
        $kb = AiChatLog::where('provider', 'knowledge_base')->count();
        $ai = AiChatLog::where('provider', 'gemini')->count();

        return view('admin.knowledge.index', [
            'categories' => KnowledgeCategory::withCount('articles')->orderBy('sort_order')->get(),
            'articles' => $articles,
            'search' => $search,
            'unanswered' => UnansweredQuestion::pending()->with('user:id,name')->latest()->take(30)->get(),
            'analytics' => [
                'total' => $total,
                'kb' => $kb,
                'ai' => $ai,
                'unanswered' => UnansweredQuestion::pending()->count(),
                'kb_rate' => $total > 0 ? round($kb / $total * 100) : 0,
                'articles' => KnowledgeArticle::count(),
            ],
            'topCategories' => KnowledgeCategory::withSum('articles as views', 'views_count')->orderByDesc('views')->take(5)->get(),
            'mostViewed' => KnowledgeArticle::orderByDesc('views_count')->take(5)->get(),
        ]);
    }

    // ---------------- Categories ----------------

    public function storeCategory(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'icon' => ['nullable', 'string', 'max:16'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], ['name.required' => 'নাম দিন']);

        KnowledgeCategory::create([
            'name' => $v['name'],
            'slug' => $this->uniqueSlug($v['name']),
            'icon' => $v['icon'] ?? null,
            'status' => 'active',
            'sort_order' => $v['sort_order'] ?? 0,
        ]);

        return back()->with('success', 'ক্যাটাগরি যুক্ত হয়েছে।');
    }

    public function updateCategory(Request $request, KnowledgeCategory $category): RedirectResponse
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'icon' => ['nullable', 'string', 'max:16'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], ['name.required' => 'নাম দিন']);

        $category->update([
            'name' => $v['name'],
            'icon' => $v['icon'] ?: $category->icon,
            'sort_order' => $v['sort_order'] ?? $category->sort_order,
        ]);

        return back()->with('success', 'ক্যাটাগরি আপডেট হয়েছে।');
    }

    public function toggleCategory(KnowledgeCategory $category): RedirectResponse
    {
        $category->update(['status' => $category->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'ক্যাটাগরির স্ট্যাটাস পরিবর্তন হয়েছে।');
    }

    public function deleteCategory(KnowledgeCategory $category): RedirectResponse
    {
        $category->articles()->update(['category_id' => null]);
        $category->delete();

        return back()->with('success', 'ক্যাটাগরি মুছে ফেলা হয়েছে। (এর আর্টিকেলগুলো অশ্রেণিবদ্ধ হয়েছে)');
    }

    // ---------------- Articles ----------------

    public function createArticle(): View
    {
        return view('admin.knowledge.article-form', [
            'article' => null,
            'categories' => KnowledgeCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        KnowledgeArticle::create($this->validateArticle($request));

        return redirect()->route('admin.knowledge.index')->with('success', 'আর্টিকেল তৈরি হয়েছে।');
    }

    public function editArticle(KnowledgeArticle $article): View
    {
        return view('admin.knowledge.article-form', [
            'article' => $article,
            'categories' => KnowledgeCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function updateArticle(Request $request, KnowledgeArticle $article): RedirectResponse
    {
        $article->update($this->validateArticle($request));

        return redirect()->route('admin.knowledge.index')->with('success', 'আর্টিকেল আপডেট হয়েছে।');
    }

    public function deleteArticle(KnowledgeArticle $article): RedirectResponse
    {
        $article->delete();

        return back()->with('success', 'আর্টিকেল মুছে ফেলা হয়েছে।');
    }

    protected function validateArticle(Request $request): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'exists:knowledge_categories,id'],
            'title' => ['required', 'string', 'max:160'],
            'question' => ['required', 'string', 'max:500'],
            'keywords' => ['nullable', 'string', 'max:300'],
            'answer' => ['required', 'string', 'max:4000'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'title.required' => 'শিরোনাম দিন',
            'question.required' => 'প্রশ্ন দিন',
            'answer.required' => 'উত্তর দিন',
        ]);
    }

    // ---------------- Unanswered questions ----------------

    /** Convert an unanswered question into a Knowledge Base article. */
    public function convertUnanswered(Request $request, UnansweredQuestion $question): RedirectResponse
    {
        $v = $request->validate([
            'category_id' => ['nullable', 'exists:knowledge_categories,id'],
            'answer' => ['required', 'string', 'max:4000'],
            'keywords' => ['nullable', 'string', 'max:300'],
        ], ['answer.required' => 'উত্তর দিন']);

        KnowledgeArticle::create([
            'category_id' => $v['category_id'] ?? null,
            'title' => Str::limit($question->question, 120),
            'question' => $question->question,
            'keywords' => $v['keywords'] ?? null,
            'answer' => $v['answer'],
            'status' => 'active',
        ]);

        $question->update(['status' => 'answered']);

        return back()->with('success', 'প্রশ্নটি জ্ঞানভান্ডারে যুক্ত হয়েছে।');
    }

    public function deleteUnanswered(UnansweredQuestion $question): RedirectResponse
    {
        $question->delete();

        return back()->with('success', 'প্রশ্নটি মুছে ফেলা হয়েছে।');
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'cat-' . Str::lower(Str::random(6));
        $slug = $base;
        $i = 1;
        while (KnowledgeCategory::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
