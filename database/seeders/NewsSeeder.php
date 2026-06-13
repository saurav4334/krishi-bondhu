<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'কৃষি সংবাদ' => 'agriculture-news',
            'সরকারি বিজ্ঞপ্তি' => 'government-circular',
            'আবহাওয়া সতর্কতা' => 'weather-alert',
            'ফসলের রোগ' => 'crop-disease',
            'সার আপডেট' => 'fertilizer-update',
            'বাজার আপডেট' => 'market-update',
        ];
        foreach ($categories as $name => $slug) {
            NewsCategory::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $posts = [
            ['cat' => 'agriculture-news', 'title' => 'চলতি মৌসুমে আমন ধানের বাম্পার ফলনের সম্ভাবনা', 'important' => false, 'district' => null,
             'desc' => "অনুকূল আবহাওয়া ও পর্যাপ্ত বৃষ্টিপাতের কারণে এ বছর আমন ধানের ভালো ফলন হওয়ার সম্ভাবনা রয়েছে বলে কৃষি সম্প্রসারণ অধিদপ্তর জানিয়েছে। কৃষকদের সময়মতো সার ও সেচ ব্যবস্থাপনার পরামর্শ দেওয়া হয়েছে।"],
            ['cat' => 'fertilizer-update', 'title' => 'ইউরিয়া, টিএসপি ও ডিএপি সারের নতুন মূল্য নির্ধারণ', 'important' => true, 'district' => null,
             'desc' => "সরকার সারের নতুন মূল্য তালিকা প্রকাশ করেছে। ডিলার পর্যায়ে ইউরিয়া প্রতি কেজি ২৭ টাকা নির্ধারণ করা হয়েছে। রশিদ ছাড়া সার না কেনার জন্য কৃষকদের অনুরোধ জানানো হয়েছে।"],
            ['cat' => 'crop-disease', 'title' => 'আলুর লেট ব্লাইট রোগ প্রতিরোধে জরুরি পরামর্শ', 'important' => true, 'district' => 'রংপুর',
             'desc' => "ঘন কুয়াশা ও আর্দ্র আবহাওয়ায় আলুর জমিতে লেট ব্লাইট রোগ ছড়ানোর আশঙ্কা রয়েছে। প্রতি লিটার পানিতে ২ গ্রাম হারে ম্যানকোজেব জাতীয় ছত্রাকনাশক স্প্রে করার পরামর্শ দেওয়া হচ্ছে।"],
            ['cat' => 'government-circular', 'title' => 'কৃষি প্রণোদনা কর্মসূচিতে বীজ ও সার বিতরণ শুরু', 'important' => false, 'district' => null,
             'desc' => "ক্ষুদ্র ও প্রান্তিক কৃষকদের মাঝে বিনামূল্যে বীজ ও সার বিতরণ কর্মসূচি শুরু হয়েছে। স্থানীয় উপজেলা কৃষি অফিসে যোগাযোগ করার অনুরোধ জানানো হয়েছে।"],
            ['cat' => 'market-update', 'title' => 'কাঁচা মরিচ ও পেঁয়াজের দাম স্থিতিশীল', 'important' => false, 'district' => null,
             'desc' => "গত সপ্তাহের তুলনায় কাঁচা মরিচ ও দেশী পেঁয়াজের বাজারদর তুলনামূলক স্থিতিশীল রয়েছে। সরবরাহ স্বাভাবিক থাকায় দাম নিয়ন্ত্রণে রয়েছে বলে ব্যবসায়ীরা জানিয়েছেন।"],
        ];

        foreach ($posts as $p) {
            $category = NewsCategory::where('slug', $p['cat'])->first();
            if (! $category) {
                continue;
            }
            NewsPost::create([
                'category_id' => $category->id,
                'title' => $p['title'],
                'slug' => (Str::slug($p['title']) ?: 'news') . '-' . Str::lower(Str::random(6)),
                'description' => $p['desc'],
                'district' => $p['district'],
                'is_important' => $p['important'],
                'status' => 'published',
                'published_at' => now()->subDays(rand(0, 6)),
            ]);
        }
    }
}
