<?php

namespace Database\Seeders;

use App\Models\CropPost;
use App\Models\Expert;
use App\Models\LaborJobPost;
use App\Models\LaborWorker;
use App\Models\MarketPrice;
use App\Models\Notification;
use App\Models\TransportProvider;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Bangladesh divisions / districts / upazilas ---
        $this->call(BangladeshGeoSeeder::class);

        // --- Users ---
        $admin = User::create([
            'name' => 'Admin',
            'mobile' => '01912345678',
            'district' => 'ঢাকা',
            'role' => 'admin',
            'password' => 'password',
        ]);

        $expert = User::create([
            'name' => 'ড. করিম হোসেন',
            'mobile' => '01812345678',
            'district' => 'রাজশাহী',
            'role' => 'expert',
            'password' => 'password',
        ]);

        $farmer = User::create([
            'name' => 'রহিম উদ্দিন',
            'mobile' => '01712345678',
            'district' => 'ঢাকা',
            'role' => 'farmer',
            'password' => 'password',
        ]);

        User::create([
            'name' => 'করিম মিয়া',
            'mobile' => '01612345678',
            'district' => 'ময়মনসিংহ',
            'role' => 'farmer',
            'password' => 'password',
        ]);

        // --- Market Prices ---
        $prices = [
            ['crop_name' => 'ধান (IRRI)', 'unit' => 'মণ', 'price' => 1050],
            ['crop_name' => 'গম', 'unit' => 'মণ', 'price' => 1400],
            ['crop_name' => 'আলু', 'unit' => 'কেজি', 'price' => 28],
            ['crop_name' => 'পাট', 'unit' => 'মণ', 'price' => 3200],
            ['crop_name' => 'পেঁয়াজ', 'unit' => 'কেজি', 'price' => 65],
            ['crop_name' => 'টমেটো', 'unit' => 'কেজি', 'price' => 45],
            ['crop_name' => 'কলা', 'unit' => 'হালি', 'price' => 40],
            ['crop_name' => 'ইলিশ মাছ', 'unit' => 'কেজি', 'price' => 1200],
            ['crop_name' => 'বেগুন', 'unit' => 'কেজি', 'price' => 35],
            ['crop_name' => 'মরিচ (কাঁচা)', 'unit' => 'কেজি', 'price' => 80],
            ['crop_name' => 'লাউ', 'unit' => 'পিস', 'price' => 50],
            ['crop_name' => 'করলা', 'unit' => 'কেজি', 'price' => 55],
        ];
        foreach ($prices as $p) {
            MarketPrice::create(array_merge($p, ['district' => 'সকল']));
        }

        // --- Experts Directory ---
        Expert::create([
            'name' => 'ড. করিম হোসেন',
            'specialization' => 'ধান ও গম বিশেষজ্ঞ',
            'district' => 'রাজশাহী',
            'mobile' => '01812345678',
            'availability' => 'সকাল ৯টা - বিকাল ৫টা',
            'experience' => '১৫ বছর',
        ]);
        Expert::create([
            'name' => 'ড. ফাতেমা বেগম',
            'specialization' => 'সবজি ও ফল বিশেষজ্ঞ',
            'district' => 'ঢাকা',
            'mobile' => '01911111111',
            'availability' => 'সকাল ১০টা - বিকাল ৪টা',
            'experience' => '১০ বছর',
        ]);
        Expert::create([
            'name' => 'আগ্রো. সালাম মিয়া',
            'specialization' => 'মাটি ও সার বিশেষজ্ঞ',
            'district' => 'চট্টগ্রাম',
            'mobile' => '01711111111',
            'availability' => 'বিকাল ৩টা - রাত ৮টা',
            'experience' => '২০ বছর',
        ]);
        Expert::create([
            'name' => 'ড. রাহেলা খানম',
            'specialization' => 'পোকামাকড় দমন বিশেষজ্ঞ',
            'district' => 'খুলনা',
            'mobile' => '01611111111',
            'availability' => 'সকাল ৮টা - দুপুর ২টা',
            'experience' => '১২ বছর',
        ]);

        // --- Sample Crop Posts ---
        CropPost::create([
            'user_id' => $farmer->id,
            'crop_name' => 'আমন ধান',
            'quantity' => '50 মণ',
            'price' => 1000,
            'location' => 'ময়মনসিংহ',
            'mobile' => '01712345678',
            'description' => 'তাজা আমন ধান বিক্রি হবে। ভালো মানের, সরাসরি খামার থেকে।',
            'status' => 'active',
        ]);
        CropPost::create([
            'user_id' => $farmer->id,
            'crop_name' => 'লাল আলু',
            'quantity' => '200 কেজি',
            'price' => 25,
            'location' => 'বগুড়া',
            'mobile' => '01712345678',
            'description' => 'ডায়মন্ড জাতের আলু। সরাসরি খামার থেকে।',
            'status' => 'active',
        ]);

        // --- Sample Notifications ---
        Notification::create([
            'title' => 'বন্যা সতর্কতা',
            'message' => 'উত্তরাঞ্চলে বন্যার আশঙ্কা। ফসল নিরাপদ স্থানে সরান।',
            'user_type' => 'all',
        ]);
        Notification::create([
            'title' => 'বাজার আপডেট',
            'message' => 'আলুর দাম ৫% বৃদ্ধি পেয়েছে।',
            'user_type' => 'all',
        ]);
        Notification::create([
            'title' => 'নতুন বিশেষজ্ঞ',
            'message' => 'প্ল্যাটফর্মে নতুন কৃষি বিশেষজ্ঞ যোগ দিয়েছেন।',
            'user_type' => 'all',
        ]);

        // --- Labor Workers ---
        $workers = [
            ['name' => 'করিম শেখ', 'mobile' => '01710000001', 'district' => 'ময়মনসিংহ', 'area' => 'ত্রিশাল', 'skill_type' => 'ধান কাটার শ্রমিক', 'daily_wage' => 700, 'experience' => '৮ বছর', 'availability_status' => 'available'],
            ['name' => 'জসিম উদ্দিন', 'mobile' => '01710000002', 'district' => 'বগুড়া', 'area' => 'শেরপুর', 'skill_type' => 'স্প্রে শ্রমিক', 'daily_wage' => 600, 'experience' => '৫ বছর', 'availability_status' => 'available'],
            ['name' => 'রফিক মিয়া', 'mobile' => '01710000003', 'district' => 'রংপুর', 'area' => 'মিঠাপুকুর', 'skill_type' => 'জমি প্রস্তুত শ্রমিক', 'daily_wage' => 800, 'experience' => '১০ বছর', 'availability_status' => 'busy'],
            ['name' => 'সেলিম হোসেন', 'mobile' => '01710000004', 'district' => 'রাজশাহী', 'area' => 'পবা', 'skill_type' => 'সবজি শ্রমিক', 'daily_wage' => 550, 'experience' => '৩ বছর', 'availability_status' => 'available'],
        ];
        foreach ($workers as $w) {
            LaborWorker::create(array_merge($w, ['user_id' => $farmer->id]));
        }

        // --- Labor Job Posts ---
        LaborJobPost::create([
            'farmer_id' => $farmer->id,
            'job_type' => 'ধান কাটার শ্রমিক',
            'location' => 'ময়মনসিংহ, ত্রিশাল',
            'worker_needed' => 5,
            'wage' => 750,
            'duration' => '৩ দিন',
            'contact_number' => '01712345678',
            'status' => 'open',
        ]);

        // --- Transport Providers ---
        $providers = [
            ['driver_name' => 'আব্দুল হক', 'mobile' => '01720000001', 'vehicle_type' => 'পিকআপ ভ্যান', 'vehicle_number' => 'ঢাকা মেট্রো-১১', 'district' => 'ঢাকা', 'service_area' => 'ঢাকা - ময়মনসিংহ', 'rate_per_km' => 35, 'availability_status' => 'available'],
            ['driver_name' => 'সোহেল রানা', 'mobile' => '01720000002', 'vehicle_type' => 'মিনি ট্রাক', 'vehicle_number' => 'রাজ-১২৩৪', 'district' => 'রাজশাহী', 'service_area' => 'উত্তরবঙ্গ', 'rate_per_km' => 45, 'availability_status' => 'available'],
            ['driver_name' => 'নয়ন মিয়া', 'mobile' => '01720000003', 'vehicle_type' => 'ট্রাক্টর', 'vehicle_number' => null, 'district' => 'বগুড়া', 'service_area' => 'বগুড়া সদর', 'rate_per_km' => 60, 'availability_status' => 'busy'],
        ];
        foreach ($providers as $p) {
            TransportProvider::create(array_merge($p, ['user_id' => $farmer->id]));
        }

        $this->command->info('');
        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('Demo Accounts (password: "password"):');
        $this->command->info('  Admin   : 01912345678');
        $this->command->info('  Expert  : 01812345678');
        $this->command->info('  Farmer  : 01712345678');
        $this->command->info('');
    }
}
