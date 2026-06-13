<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function __construct(protected SmsService $sms)
    {
    }

    public function index(): View
    {
        return view('admin.news.index', [
            'posts' => NewsPost::with('category')->latest()->paginate(15),
            'categories' => NewsCategory::withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.news.form', [
            'post' => new NewsPost(),
            'categories' => NewsCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['image'] = $this->storeImage($request);

        NewsPost::create($data);

        return redirect()->route('admin.news.index')->with('success', 'সংবাদ প্রকাশ করা হয়েছে।');
    }

    public function edit(NewsPost $news): View
    {
        return view('admin.news.form', [
            'post' => $news,
            'categories' => NewsCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, NewsPost $news): RedirectResponse
    {
        $data = $this->validateData($request);

        if ($data['title'] !== $news->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $news->id);
        }

        if ($request->hasFile('image')) {
            if ($news->image) {
                Storage::disk('public')->delete($news->image);
            }
            $data['image'] = $this->storeImage($request);
        }

        $news->update($data);

        return redirect()->route('admin.news.index')->with('success', 'সংবাদ আপডেট হয়েছে।');
    }

    public function destroy(NewsPost $news): RedirectResponse
    {
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }
        $news->delete();

        return back()->with('success', 'সংবাদ মুছে ফেলা হয়েছে।');
    }

    /** Manually SMS a news item to farmers (district-targeted, else all). */
    public function sendSms(NewsPost $news): RedirectResponse
    {
        $message = 'কৃষি সংবাদ: ' . Str::limit($news->title, 140);
        $sent = 0;

        User::where('role', 'farmer')
            ->when($news->district, fn ($q) => $q->where('district', $news->district))
            ->select('mobile')->chunk(100, function ($chunk) use ($message, &$sent) {
                $mobiles = $chunk->pluck('mobile')->all();
                $this->sms->send($mobiles, $message, 'news');
                $sent += count($mobiles);
            });

        return back()->with('success', $sent ? "{$sent} জন কৃষককে SMS পাঠানো হয়েছে।" : 'কোনো প্রাপক পাওয়া যায়নি।');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:50']], ['name.required' => 'নাম দিন']);

        NewsCategory::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueCategorySlug($validated['name']),
        ]);

        return back()->with('success', 'ক্যাটাগরি যুক্ত হয়েছে।');
    }

    public function deleteCategory(NewsCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'ক্যাটাগরি মুছে ফেলা হয়েছে।');
    }

    protected function validateData(Request $request): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:news_categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'district' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:published,draft'],
            'published_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ], [
            'category_id.required' => 'ক্যাটাগরি নির্বাচন করুন',
            'title.required' => 'শিরোনাম দিন',
            'description.required' => 'বিবরণ দিন',
        ]);

        return [
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'district' => $validated['district'] ?? null,
            'is_important' => $request->boolean('is_important'),
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? now(),
        ];
    }

    protected function storeImage(Request $request): ?string
    {
        return $request->hasFile('image')
            ? $request->file('image')->store('news', 'public')
            : null;
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'news-' . Str::lower(Str::random(8));
        $slug = $base;
        $i = 1;
        while (NewsPost::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    protected function uniqueCategorySlug(string $name): string
    {
        $base = Str::slug($name) ?: 'cat-' . Str::lower(Str::random(6));
        $slug = $base;
        $i = 1;
        while (NewsCategory::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
