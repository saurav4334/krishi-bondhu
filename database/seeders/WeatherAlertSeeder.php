<?php

namespace Database\Seeders;

use App\Models\WeatherAlert;
use Illuminate\Database\Seeder;

class WeatherAlertSeeder extends Seeder
{
    public function run(): void
    {
        $alerts = [
            ['district' => 'রংপুর', 'alert_type' => 'heavy_rain', 'severity' => 'high', 'title' => 'আগামী ৪৮ ঘণ্টায় ভারী বৃষ্টির সম্ভাবনা',
             'description' => 'রংপুর অঞ্চলে আগামী দুই দিন মাঝারি থেকে ভারী বৃষ্টিপাতের সম্ভাবনা রয়েছে। পরিপক্ক ধান দ্রুত কেটে নিরাপদ স্থানে সংরক্ষণের পরামর্শ দেওয়া হচ্ছে।', 'alert_date' => now()->addDay()->toDateString()],
            ['district' => 'ঢাকা', 'alert_type' => 'heat_wave', 'severity' => 'moderate', 'title' => 'মৃদু তাপপ্রবাহ বয়ে যাচ্ছে',
             'description' => 'তাপমাত্রা স্বাভাবিকের চেয়ে বেশি থাকতে পারে। ফসলের জমিতে পর্যাপ্ত সেচ নিশ্চিত করুন এবং দুপুরে গবাদিপশুকে ছায়ায় রাখুন।', 'alert_date' => now()->toDateString()],
            ['district' => 'ময়মনসিংহ', 'alert_type' => 'thunderstorm', 'severity' => 'moderate', 'title' => 'বজ্রসহ ঝড়ের সতর্কতা',
             'description' => 'বিকেলের দিকে বজ্রসহ ঝড়ো হাওয়ার সম্ভাবনা। খোলা মাঠে কাজ করার সময় সতর্ক থাকুন এবং নিরাপদ আশ্রয়ে থাকুন।', 'alert_date' => now()->addDays(2)->toDateString()],
            ['district' => 'রাজশাহী', 'alert_type' => 'flood', 'severity' => 'severe', 'title' => 'নিম্নাঞ্চলে জলাবদ্ধতার আশঙ্কা',
             'description' => 'টানা বৃষ্টিতে নিচু এলাকায় জলাবদ্ধতা তৈরি হতে পারে। সবজি খেতের পানি নিষ্কাশনের ব্যবস্থা আগেভাগে নিন।', 'alert_date' => now()->addDay()->toDateString()],
        ];

        foreach ($alerts as $alert) {
            WeatherAlert::create($alert);
        }
    }
}
