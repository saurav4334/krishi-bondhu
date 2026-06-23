<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\EquipmentCategory;
use App\Models\EquipmentProduct;
use App\Models\Upazila;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * কৃষি সরঞ্জাম — Equipment & agricultural-input marketplace.
 * Fully independent from the crop trading module (PostController / crop_posts).
 */
class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $mainSlug = $request->category;
        $subSlug = $request->sub;

        $products = EquipmentProduct::published()
            ->with(['user:id,name', 'category', 'images'])
            ->when($subSlug, function ($q, $slug) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug));
            })
            ->when(! $subSlug && $mainSlug, function ($q) use ($mainSlug) {
                $main = EquipmentCategory::where('slug', $mainSlug)->first();
                if ($main) {
                    $ids = $main->children()->pluck('id')->push($main->id)->all();
                    $q->whereIn('category_id', $ids);
                }
            })
            ->when($request->district, fn ($q, $d) => $q->where('location', $d))
            ->when($request->q, function ($q, $term) {
                $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhere('model', 'like', "%{$term}%"));
            })
            ->orderByDesc('featured')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $mains = EquipmentCategory::mains()->active()
            ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return view('equipment.index', [
            'products' => $products,
            'mains' => $mains,
            'activeMain' => $mainSlug ? $mains->firstWhere('slug', $mainSlug) : null,
            'districts' => District::active()->orderBy('bn_name')->get(),
            'filters' => $request->only('category', 'sub', 'district', 'q'),
        ]);
    }

    public function create(): View
    {
        return view('equipment.create', [
            'mains' => EquipmentCategory::mains()->active()
                ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get(),
            'districts' => District::active()->orderBy('bn_name')->get(['id', 'bn_name']),
            'upazilas' => Upazila::orderBy('bn_name')->get(['id', 'district_id', 'bn_name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:equipment_categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'min:1'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:30'],
            'condition' => ['nullable', 'in:new,used'],
            'location' => ['required', 'string', 'max:100'],
            'upazila' => ['nullable', 'string', 'max:60'],
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'whatsapp' => ['nullable', 'regex:/^01[3-9][0-9]{8}$/'],
            'description' => ['nullable', 'string', 'max:1500'],
            'images' => ['required', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpeg,png,webp', 'max:10240'],
        ], [
            'category_id.required' => 'ক্যাটাগরি নির্বাচন করুন',
            'name.required' => 'পণ্যের নাম দিন',
            'price.required' => 'দাম দিন',
            'price.numeric' => 'সঠিক দাম দিন',
            'location.required' => 'জেলা নির্বাচন করুন',
            'mobile.required' => 'মোবাইল নম্বর দিন',
            'mobile.regex' => 'সঠিক বাংলাদেশী মোবাইল নম্বর দিন',
            'whatsapp.regex' => 'সঠিক বাংলাদেশী WhatsApp নম্বর দিন',
            'images.required' => 'অন্তত একটি ছবি দিন',
            'images.*.image' => 'শুধুমাত্র ছবি আপলোড করুন',
            'images.*.max' => 'প্রতিটি ছবির আকার সর্বোচ্চ ১০MB',
        ]);

        $product = EquipmentProduct::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'brand' => $validated['brand'] ?? null,
            'model' => $validated['model'] ?? null,
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'condition' => $validated['condition'] ?? null,
            'location' => $validated['location'],
            'upazila' => $validated['upazila'] ?? null,
            'mobile' => $validated['mobile'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => 'active',
            'approved' => false, // pending admin approval
        ]);

        $this->storeImages($product, $request->file('images', []));

        return redirect()->route('equipment.index')
            ->with('success', 'পণ্য জমা হয়েছে! অ্যাডমিন অনুমোদনের পর এটি প্রকাশিত হবে।');
    }

    public function show(EquipmentProduct $product): View
    {
        $isPublic = $product->status === 'active' && $product->approved;
        if (! $isPublic) {
            $user = Auth::user();
            if (! $user || ($user->id !== $product->user_id && ! $user->isAdmin())) {
                abort(404);
            }
        }

        $product->load(['user:id,name', 'category.parent', 'images']);

        return view('equipment.show', ['product' => $product]);
    }

    public function destroy(EquipmentProduct $product): RedirectResponse
    {
        if ($product->user_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'অনুমতি নেই');
        }

        $product->update(['status' => 'deleted']);

        return back()->with('success', 'পণ্য মুছে ফেলা হয়েছে');
    }

    /**
     * Store uploaded images (skipping duplicates within the submission by hash).
     * First image is mirrored into equipment_products.image for list thumbnails.
     */
    protected function storeImages(EquipmentProduct $product, array $files): void
    {
        $seen = [];
        $first = true;

        foreach ($files as $file) {
            $hash = sha1_file($file->getRealPath());
            if (in_array($hash, $seen, true)) {
                continue;
            }
            $seen[] = $hash;

            $path = $file->store('equipment', 'public');
            $product->images()->create(['image' => $path]);

            if ($first) {
                $product->update(['image' => $path]);
                $first = false;
            }
        }
    }
}
