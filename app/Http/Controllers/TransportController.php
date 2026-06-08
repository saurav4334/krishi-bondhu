<?php

namespace App\Http\Controllers;

use App\Models\TransportBooking;
use App\Models\TransportProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransportController extends Controller
{
    /** Vehicle categories. */
    public const VEHICLES = [
        'পিকআপ ভ্যান',
        'মিনি ট্রাক',
        'ট্রাক',
        'কভার্ড ভ্যান',
        'ট্রাক্টর',
        'কোল্ড স্টোরেজ পরিবহন',
    ];

    public function index(Request $request): View
    {
        $providers = TransportProvider::query()
            ->when($request->filled('district'), fn ($q) => $q->where('district', 'like', '%' . $request->district . '%'))
            ->when($request->filled('vehicle_type'), fn ($q) => $q->where('vehicle_type', $request->vehicle_type))
            ->orderByRaw("availability_status = 'available' DESC")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $bookings = TransportBooking::where('user_id', Auth::id())
            ->with('provider:id,driver_name,vehicle_type')
            ->latest()
            ->take(10)
            ->get();

        return view('transport.index', [
            'providers' => $providers,
            'bookings' => $bookings,
            'vehicles' => self::VEHICLES,
            'filters' => $request->only('district', 'vehicle_type'),
        ]);
    }

    public function register(): View
    {
        return view('transport.register', ['vehicles' => self::VEHICLES]);
    }

    public function storeProvider(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'driver_name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'vehicle_type' => ['required', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'district' => ['required', 'string', 'max:50'],
            'service_area' => ['nullable', 'string', 'max:150'],
            'rate_per_km' => ['nullable', 'numeric', 'min:0'],
            'availability_status' => ['required', 'in:available,busy'],
        ], [
            'driver_name.required' => 'ড্রাইভারের নাম দিন',
            'mobile.required' => 'মোবাইল নম্বর দিন',
            'mobile.regex' => 'সঠিক বাংলাদেশী মোবাইল নম্বর দিন',
            'vehicle_type.required' => 'গাড়ির ধরন নির্বাচন করুন',
            'district.required' => 'জেলা দিন',
            'rate_per_km.numeric' => 'সঠিক রেট দিন',
        ]);

        $validated['user_id'] = Auth::id();

        TransportProvider::create($validated);

        return redirect()->route('transport.index')->with('success', 'পরিবহন প্রোফাইল তৈরি হয়েছে!');
    }

    public function book(TransportProvider $provider = null): View
    {
        return view('transport.book', ['provider' => $provider]);
    }

    public function storeBooking(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transport_provider_id' => ['nullable', 'exists:transport_providers,id'],
            'pickup_location' => ['required', 'string', 'max:150'],
            'delivery_location' => ['required', 'string', 'max:150'],
            'crop_type' => ['nullable', 'string', 'max:100'],
            'quantity' => ['nullable', 'string', 'max:50'],
            'booking_date' => ['nullable', 'date', 'after_or_equal:today'],
            'contact_number' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
        ], [
            'pickup_location.required' => 'পিকআপ লোকেশন দিন',
            'delivery_location.required' => 'ডেলিভারি লোকেশন দিন',
            'booking_date.after_or_equal' => 'তারিখ আজ বা পরবর্তী হতে হবে',
            'contact_number.required' => 'যোগাযোগ নম্বর দিন',
            'contact_number.regex' => 'সঠিক বাংলাদেশী মোবাইল নম্বর দিন',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'pending';

        TransportBooking::create($validated);

        return redirect()->route('transport.index')->with('success', 'বুকিং রিকোয়েস্ট পাঠানো হয়েছে!');
    }
}
