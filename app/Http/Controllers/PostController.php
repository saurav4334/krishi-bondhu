<?php

namespace App\Http\Controllers;

use App\Models\CropPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = CropPost::active()
            ->with('user:id,name')
            ->latest()
            ->paginate(20);

        return view('market.index', compact('posts'));
    }

    public function create(): View
    {
        return view('market.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'crop_name' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:1'],
            'location' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ], [
            'crop_name.required' => 'ফসলের নাম দিন',
            'quantity.required' => 'পরিমাণ দিন',
            'price.required' => 'দাম দিন',
            'price.numeric' => 'সঠিক দাম দিন',
            'location.required' => 'অবস্থান দিন',
            'mobile.required' => 'মোবাইল নম্বর দিন',
            'mobile.regex' => 'সঠিক বাংলাদেশী মোবাইল নম্বর দিন',
            'image.image' => 'শুধুমাত্র ছবি আপলোড করুন',
            'image.max' => 'ছবির আকার সর্বোচ্চ ২MB',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('posts', 'public');
        }

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'active';

        CropPost::create($validated);

        return redirect()->route('market.index')->with('success', 'পোস্ট প্রকাশিত হয়েছে!');
    }

    public function destroy(CropPost $post): RedirectResponse
    {
        if ($post->user_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'অনুমতি নেই');
        }

        $post->update(['status' => 'deleted']);

        return back()->with('success', 'পোস্ট মুছে ফেলা হয়েছে');
    }
}
