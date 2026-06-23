<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentCategory;
use App\Models\EquipmentProduct;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function __construct(protected SmsService $sms)
    {
    }

    public function index(Request $request): View
    {
        $filter = $request->input('filter', 'pending'); // pending | approved | all

        $products = EquipmentProduct::where('status', 'active')
            ->when($filter === 'pending', fn ($q) => $q->where('approved', false))
            ->when($filter === 'approved', fn ($q) => $q->where('approved', true))
            ->with(['user:id,name', 'category'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.equipment.index', [
            'products' => $products,
            'filter' => $filter,
            'categoryTree' => EquipmentCategory::mains()
                ->with(['children' => fn ($q) => $q->withCount('products')->orderBy('sort_order')])
                ->withCount('products')
                ->orderBy('sort_order')
                ->get(),
            'mains' => EquipmentCategory::mains()->orderBy('sort_order')->get(),
        ]);
    }

    public function approve(EquipmentProduct $product): RedirectResponse
    {
        $product->update(['approved' => true]);

        $this->sms->send(
            $product->mobile,
            "আপনার পণ্য '" . Str::limit($product->name, 60) . "' অনুমোদিত হয়েছে। — কৃষি-বন্ধু",
            'equipment',
            $product->user_id
        );

        return back()->with('success', 'পণ্য অনুমোদিত হয়েছে। বিক্রেতাকে SMS পাঠানো হয়েছে।');
    }

    public function reject(EquipmentProduct $product): RedirectResponse
    {
        $product->update(['status' => 'deleted']);

        return back()->with('success', 'পণ্য বাতিল করা হয়েছে।');
    }

    public function feature(EquipmentProduct $product): RedirectResponse
    {
        $product->update(['featured' => ! $product->featured]);

        return back()->with('success', $product->featured ? 'ফিচার্ড করা হয়েছে।' : 'ফিচার্ড সরানো হয়েছে।');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'icon' => ['nullable', 'string', 'max:16'],
            'parent_id' => ['nullable', 'exists:equipment_categories,id'],
        ], [
            'name.required' => 'ক্যাটাগরির নাম দিন',
        ]);

        EquipmentCategory::create([
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'icon' => $validated['icon'] ?? null,
            'status' => 'active',
            'sort_order' => 99,
        ]);

        return back()->with('success', 'ক্যাটাগরি যুক্ত হয়েছে।');
    }

    public function toggleCategory(EquipmentCategory $category): RedirectResponse
    {
        $category->update(['status' => $category->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'ক্যাটাগরির স্ট্যাটাস পরিবর্তন হয়েছে।');
    }

    public function deleteCategory(EquipmentCategory $category): RedirectResponse
    {
        // Remove subcategories too so none are left orphaned.
        $category->children()->delete();
        $category->delete();

        return back()->with('success', 'ক্যাটাগরি মুছে ফেলা হয়েছে।');
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'cat-' . Str::lower(Str::random(6));
        $slug = $base;
        $i = 1;
        while (EquipmentCategory::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
