<?php

namespace App\Http\Controllers;

use App\Models\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $userDistrict = Auth::user()->district;

        $posts = NewsPost::published()
            ->with('category')
            ->when($request->category, function ($q, $slug) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug));
            })
            ->where(fn ($q) => $q->whereNull('district')->orWhere('district', $userDistrict))
            ->orderByDesc('is_important')
            ->orderByDesc('published_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $important = NewsPost::published()
            ->where('is_important', true)
            ->where(fn ($q) => $q->whereNull('district')->orWhere('district', $userDistrict))
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('news.index', [
            'posts' => $posts,
            'categories' => NewsCategory::orderBy('name')->get(),
            'important' => $important,
            'activeCategory' => $request->category,
        ]);
    }

    public function show(NewsPost $post): View
    {
        abort_unless($post->status === 'published', 404);

        $post->load('category');

        $related = NewsPost::published()
            ->where('category_id', $post->category_id)
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->take(4)
            ->get();

        return view('news.show', compact('post', 'related'));
    }
}
