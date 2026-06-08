<?php

namespace Database\Seeders;

use App\Models\CropPost;
use App\Models\Expert;
use App\Models\MarketPrice;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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
