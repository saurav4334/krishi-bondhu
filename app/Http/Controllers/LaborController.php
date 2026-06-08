<?php

namespace App\Http\Controllers;

use App\Models\LaborJobPost;
use App\Models\LaborWorker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LaborController extends Controller
{
    /** Labour skill categories (Bengali). */
    public const SKILLS = [
        'ধান কাটার শ্রমিক',
        'জমি প্রস্তুত শ্রমিক',
        'সবজি শ্রমিক',
        'স্প্রে শ্রমিক',
        'সেচ শ্রমিক',
        'ফসল পরিবহন শ্রমিক',
    ];

    public function index(Request $request): View
    {
        $workers = LaborWorker::query()
            ->when($request->filled('district'), fn ($q) => $q->where('district', 'like', '%' . $request->district . '%'))
            ->when($request->filled('skill_type'), fn ($q) => $q->where('skill_type', $request->skill_type))
            ->orderByRaw("availability_status = 'available' DESC")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $jobs = LaborJobPost::open()
            ->with('farmer:id,name')
            ->latest()
            ->take(10)
            ->get();

        return view('labor.index', [
            'workers' => $workers,
            'jobs' => $jobs,
            'skills' => self::SKILLS,
            'filters' => $request->only('district', 'skill_type'),
        ]);
    }

    public function register(): View
    {
        return view('labor.register', ['skills' => self::SKILLS]);
    }

    public function storeWorker(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'district' => ['required', 'string', 'max:50'],
            'area' => ['nullable', 'string', 'max:100'],
            'skill_type' => ['required', 'string', 'max:100'],
            'daily_wage' => ['required', 'numeric', 'min:1'],
            'experience' => ['nullable', 'string', 'max:50'],
            'availability_status' => ['required', 'in:available,busy'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'নাম দিন',
            'mobile.required' => 'মোবাইল নম্বর দিন',
            'mobile.regex' => 'সঠিক বাংলাদেশী মোবাইল নম্বর দিন',
            'district.required' => 'জেলা দিন',
            'skill_type.required' => 'কাজের ধরন নির্বাচন করুন',
            'daily_wage.required' => 'দৈনিক মজুরি দিন',
            'daily_wage.numeric' => 'সঠিক মজুরি দিন',
            'image.image' => 'শুধুমাত্র ছবি আপলোড করুন',
            'image.max' => 'ছবির আকার সর্বোচ্চ ২MB',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('labor', 'public');
        }

        $validated['user_id'] = Auth::id();

        LaborWorker::create($validated);

        return redirect()->route('labor.index')->with('success', 'শ্রমিক প্রোফাইল তৈরি হয়েছে!');
    }

    public function createJob(): View
    {
        return view('labor.jobs-create', ['skills' => self::SKILLS]);
    }

    public function storeJob(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_type' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:100'],
            'worker_needed' => ['required', 'integer', 'min:1', 'max:500'],
            'wage' => ['required', 'numeric', 'min:1'],
            'duration' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
        ], [
            'job_type.required' => 'কাজের ধরন নির্বাচন করুন',
            'location.required' => 'লোকেশন দিন',
            'worker_needed.required' => 'প্রয়োজনীয় শ্রমিক সংখ্যা দিন',
            'wage.required' => 'মজুরি দিন',
            'wage.numeric' => 'সঠিক মজুরি দিন',
            'contact_number.required' => 'যোগাযোগ নম্বর দিন',
            'contact_number.regex' => 'সঠিক বাংলাদেশী মোবাইল নম্বর দিন',
        ]);

        $validated['farmer_id'] = Auth::id();
        $validated['status'] = 'open';

        LaborJobPost::create($validated);

        return redirect()->route('labor.index')->with('success', 'কাজের পোস্ট প্রকাশিত হয়েছে!');
    }
}
