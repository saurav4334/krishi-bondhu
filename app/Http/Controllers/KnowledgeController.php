<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Farmer-facing কৃষি জ্ঞানভান্ডার: browse by category, search, popular answers.
 */
class KnowledgeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q'));
        $categorySlug = $request->input('category');

        $articles = KnowledgeArticle::active()
            ->with('category')
            ->when($categorySlug, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
            ->when($search, function ($q) use ($search) {
                $q->where(fn ($w) => $w->where('title', 'like', "%{$search}%")
                    ->orWhere('question', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%"));
            })
            ->when($search || $categorySlug, fn ($q) => $q->orderByDesc('views_count'))
            ->when(! $search && ! $categorySlug, fn ($q) => $q->orderByDesc('views_count'))
            ->paginate(15)
            ->withQueryString();

        return view('knowledge.index', [
            'articles' => $articles,
            'categories' => KnowledgeCategory::active()->withCount(['articles' => fn ($q) => $q->where('status', 'active')])
                ->orderBy('sort_order')->get(),
            'popular' => KnowledgeArticle::active()->orderByDesc('views_count')->take(6)->get(),
            'filters' => ['q' => $search, 'category' => $categorySlug],
        ]);
    }

    public function show(KnowledgeArticle $article): View
    {
        abort_unless($article->status === 'active', 404);
        $article->increment('views_count');

        return view('knowledge.show', [
            'article' => $article->load('category'),
            'related' => KnowledgeArticle::active()
                ->where('category_id', $article->category_id)
                ->where('id', '!=', $article->id)
                ->take(5)->get(),
        ]);
    }

    public function helpful(KnowledgeArticle $article): RedirectResponse
    {
        $article->increment('helpful_count');

        return back()->with('success', 'ধন্যবাদ! আপনার মতামত গ্রহণ করা হয়েছে।');
    }
}
