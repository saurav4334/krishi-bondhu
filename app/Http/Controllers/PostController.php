<?php

namespace App\Http\Controllers;

use App\Models\CropPost;
use App\Models\District;
use App\Models\MarketplaceCategory;
use App\Models\Upazila;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = CropPost::published()
            ->with(['user:id,name', 'category', 'images'])
            ->when($request->category, function ($q, $slug) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug));
            })
            ->when($request->district, fn ($q, $d) => $q->where('location', $d))
            ->when($request->q, fn ($q, $term) => $q->where('crop_name', 'like', "%{$term}%"))
            ->orderByDesc('featured')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('market.index', [
            'posts' => $posts,
            'categories' => MarketplaceCategory::active()->orderBy('name')->get(),
            'districts' => District::active()->orderBy('bn_name')->get(),
            'filters' => $request->only('category', 'district', 'q'),
        ]);
    }

    public function create(): View
    {
        return view('market.create', [
            'categories' => MarketplaceCategory::active()->orderBy('name')->get(),
            'districts' => District::active()->orderBy('bn_name')->get(['id', 'bn_name']),
            'upazilas' => Upazila::orderBy('bn_name')->get(['id', 'district_id', 'bn_name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:marketplace_categories,id'],
            'crop_name' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:1'],
            'condition' => ['nullable', 'in:new,used'],
            'location' => ['required', 'string', 'max:100'],
            'upazila' => ['nullable', 'string', 'max:60'],
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'images' => ['required', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,png,webp', 'max:10240'],
        ], [
            'category_id.required' => 'ক্যাটাগরি নির্বাচন করুন',
            'crop_name.required' => 'পণ্যের নাম দিন',
            'quantity.required' => 'পরিমাণ দিন',
            'price.required' => 'দাম দিন',
            'price.numeric' => 'সঠিক দাম দিন',
            'location.required' => 'জেলা নির্বাচন করুন',
            'mobile.required' => 'মোবাইল নম্বর দিন',
            'mobile.regex' => 'সঠিক বাংলাদেশী মোবাইল নম্বর দিন',
            'images.required' => 'অন্তত একটি ছবি দিন',
            'images.*.image' => 'শুধুমাত্র ছবি আপলোড করুন',
            'images.*.max' => 'প্রতিটি ছবির আকার সর্বোচ্চ ১০MB',
        ]);

        $post = CropPost::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'crop_name' => $validated['crop_name'],
            'quantity' => $validated['quantity'],
            'price' => $validated['price'],
            'condition' => $validated['condition'] ?? null,
            'location' => $validated['location'],
            'upazila' => $validated['upazila'] ?? null,
            'mobile' => $validated['mobile'],
            'description' => $validated['description'] ?? null,
            'status' => 'active',
            'approved' => false, // pending admin approval
        ]);

        $this->storeImages($post, $request->file('images', []));

        return redirect()->route('market.index')
            ->with('success', 'পোস্ট জমা হয়েছে! অ্যাডমিন অনুমোদনের পর এটি প্রকাশিত হবে।');
    }

    public function destroy(CropPost $post): RedirectResponse
    {
        if ($post->user_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'অনুমতি নেই');
        }

        $post->update(['status' => 'deleted']);

        return back()->with('success', 'পোস্ট মুছে ফেলা হয়েছে');
    }

    /**
     * Store uploaded images (skipping duplicates within the submission by hash).
     * First image is mirrored into crop_posts.image for list thumbnails.
     */
    protected function storeImages(CropPost $post, array $files): void
    {
        $seen = [];
        $first = true;

        foreach ($files as $file) {
            $hash = sha1_file($file->getRealPath());
            if (in_array($hash, $seen, true)) {
                continue;
            }
            $seen[] = $hash;

            $path = $file->store('posts', 'public');
            $post->images()->create(['image' => $path]);

            if ($first) {
                $post->update(['image' => $path]);
                $first = false;
            }
        }
    }
}
