<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CropPost;
use App\Models\MarketplaceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->input('filter', 'pending'); // pending | approved | all

        $posts = CropPost::where('status', 'active')
            ->when($filter === 'pending', fn ($q) => $q->where('approved', false))
            ->when($filter === 'approved', fn ($q) => $q->where('approved', true))
            ->with(['user:id,name', 'category'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.marketplace.index', [
            'posts' => $posts,
            'filter' => $filter,
            'categories' => MarketplaceCategory::withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function approve(CropPost $post): RedirectResponse
    {
        $post->update(['approved' => true]);

        return back()->with('success', 'পোস্ট অনুমোদিত হয়েছে।');
    }

    public function reject(CropPost $post): RedirectResponse
    {
        $post->update(['status' => 'deleted']);

        return back()->with('success', 'পোস্ট বাতিল করা হয়েছে।');
    }

    public function feature(CropPost $post): RedirectResponse
    {
        $post->update(['featured' => ! $post->featured]);

        return back()->with('success', $post->featured ? 'ফিচার্ড করা হয়েছে।' : 'ফিচার্ড সরানো হয়েছে।');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'icon' => ['nullable', 'string', 'max:16'],
        ], [
            'name.required' => 'ক্যাটাগরির নাম দিন',
        ]);

        MarketplaceCategory::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'icon' => $validated['icon'] ?? null,
            'status' => 'active',
        ]);

        return back()->with('success', 'ক্যাটাগরি যুক্ত হয়েছে।');
    }

    public function toggleCategory(MarketplaceCategory $category): RedirectResponse
    {
        $category->update(['status' => $category->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'ক্যাটাগরির স্ট্যাটাস পরিবর্তন হয়েছে।');
    }

    public function deleteCategory(MarketplaceCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'ক্যাটাগরি মুছে ফেলা হয়েছে।');
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'cat-' . Str::lower(Str::random(6));
        $slug = $base;
        $i = 1;
        while (MarketplaceCategory::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
