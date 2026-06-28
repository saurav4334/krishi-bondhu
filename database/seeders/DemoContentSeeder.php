<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\EquipmentProduct;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Extra demo content: more কৃষি সংবাদ (news) posts and কৃষি সরঞ্জাম products.
 * Idempotent — news by title (firstOrCreate), products by name (updateOrCreate)
 * — so it is safe to re-run and adds to (not replaces) existing demo data.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedNews();
        $this->seedEquipment();
    }

    private function seedNews(): void
    {
        $news = [
            ['cat' => 'agriculture-news', 'imp' => false, 'dist' => null, 't' => 'কৃষিতে ড্রোন ও স্মার্ট প্রযুক্তির ব্যবহার বাড়ছে', 'd' => 'সার ও কীটনাশক স্প্রে এবং ফসল পর্যবেক্ষণে কৃষি ড্রোনের ব্যবহার দিন দিন বাড়ছে। কৃষি সম্প্রসারণ অধিদপ্তর কৃষকদের আধুনিক প্রযুক্তি গ্রহণে উৎসাহিত করছে এবং বিভিন্ন এলাকায় প্রদর্শনী পরিচালনা করছে।'],
            ['cat' => 'weather-alert', 'imp' => true, 'dist' => 'বরিশাল', 't' => 'বঙ্গোপসাগরে লঘুচাপ, উপকূলে ভারী বৃষ্টির আশঙ্কা', 'd' => 'বঙ্গোপসাগরে সৃষ্ট লঘুচাপের প্রভাবে উপকূলীয় জেলাগুলোতে আগামী ৪৮ ঘণ্টায় ভারী বৃষ্টিপাতের সম্ভাবনা রয়েছে। কৃষকদের পাকা ধান দ্রুত কেটে নিরাপদ স্থানে রাখার পরামর্শ দেওয়া হয়েছে।'],
            ['cat' => 'government-circular', 'imp' => true, 'dist' => null, 't' => 'কৃষি যন্ত্রপাতি কিনতে ৫০-৭০% ভর্তুকির আবেদন শুরু', 'd' => 'সমলয় চাষাবাদের আওতায় ট্রাক্টর, কম্বাইন হারভেস্টার ও পাওয়ার টিলার কিনতে ৫০ থেকে ৭০ শতাংশ পর্যন্ত সরকারি ভর্তুকির জন্য আবেদন গ্রহণ শুরু হয়েছে। আগ্রহীদের উপজেলা কৃষি অফিসে যোগাযোগের অনুরোধ করা হয়েছে।'],
            ['cat' => 'crop-disease', 'imp' => true, 'dist' => 'বগুড়া', 't' => 'বোরো ধানে ব্লাস্ট রোগের প্রাদুর্ভাব, সতর্কতা জারি', 'd' => 'কয়েকটি জেলায় বোরো ধানে ব্লাস্ট রোগের প্রাদুর্ভাব দেখা দিয়েছে। আক্রান্ত জমিতে অতিরিক্ত ইউরিয়া প্রয়োগ বন্ধ রেখে ট্রাইসাইক্লাজল জাতীয় ছত্রাকনাশক স্প্রে করার পরামর্শ দেওয়া হয়েছে।'],
            ['cat' => 'fertilizer-update', 'imp' => false, 'dist' => null, 't' => 'জৈব ও ভার্মি কম্পোস্ট ব্যবহারে কৃষকদের উৎসাহ', 'd' => 'মাটির স্বাস্থ্য রক্ষায় রাসায়নিক সারের পাশাপাশি জৈব ও ভার্মি কম্পোস্ট ব্যবহারে কৃষকদের উৎসাহিত করা হচ্ছে। এতে উৎপাদন খরচ কমে এবং জমির দীর্ঘমেয়াদি উর্বরতা বজায় থাকে।'],
            ['cat' => 'market-update', 'imp' => false, 'dist' => null, 't' => 'নতুন ধান উঠতে শুরু করায় বাজারে সরবরাহ বৃদ্ধি', 'd' => 'নতুন মৌসুমের ধান বাজারে আসতে শুরু করায় সরবরাহ বেড়েছে। কৃষকদের ন্যায্যমূল্য নিশ্চিত করতে সরকারি ধান-চাল সংগ্রহ অভিযান চলমান রয়েছে।'],
            ['cat' => 'agriculture-news', 'imp' => false, 'dist' => 'রাজশাহী', 't' => 'আম রপ্তানিতে নতুন সম্ভাবনা, প্রশিক্ষণ কার্যক্রম শুরু', 'd' => 'নিরাপদ আম উৎপাদন ও রপ্তানি বাড়াতে চাষিদের ব্যাগিং পদ্ধতি ও উত্তম কৃষি চর্চা (GAP) বিষয়ে প্রশিক্ষণ দেওয়া হচ্ছে। এতে আমের গুণগত মান বৃদ্ধি পাবে।'],
            ['cat' => 'weather-alert', 'imp' => true, 'dist' => 'রংপুর', 't' => 'উত্তরাঞ্চলে শৈত্যপ্রবাহ, বীজতলা রক্ষায় পরামর্শ', 'd' => 'উত্তরাঞ্চলের কয়েকটি জেলায় মৃদু শৈত্যপ্রবাহ বইছে। বোরো বীজতলা রাতে পলিথিন দিয়ে ঢেকে রাখা এবং সকালে শিশির ঝরিয়ে দেওয়ার পরামর্শ দেওয়া হয়েছে।'],
            ['cat' => 'crop-disease', 'imp' => false, 'dist' => null, 't' => 'টমেটোর নাবিধসা রোগ প্রতিরোধে করণীয়', 'd' => 'মেঘলা ও স্যাঁতসেঁতে আবহাওয়ায় টমেটোর নাবিধসা (লেট ব্লাইট) রোগ বাড়ে। আক্রান্ত পাতা-ফল অপসারণ করে ম্যানকোজেব বা কপারজাতীয় ছত্রাকনাশক স্প্রে করার পরামর্শ দেওয়া হয়েছে।'],
            ['cat' => 'government-circular', 'imp' => false, 'dist' => null, 't' => 'প্রান্তিক কৃষকদের জন্য ৪% সুদে কৃষি ঋণ', 'd' => 'ক্ষুদ্র ও প্রান্তিক কৃষকদের জন্য মাত্র ৪ শতাংশ সুদে কৃষি ঋণ বিতরণ কার্যক্রম চলছে। জাতীয় পরিচয়পত্র ও জমির কাগজসহ নিকটস্থ কৃষি ব্যাংক শাখায় যোগাযোগের অনুরোধ জানানো হয়েছে।'],
        ];

        foreach ($news as $n) {
            $category = NewsCategory::where('slug', $n['cat'])->first();
            if (! $category) {
                continue;
            }
            NewsPost::firstOrCreate(
                ['title' => $n['t']],
                [
                    'category_id' => $category->id,
                    'slug' => 'news-' . Str::lower(Str::random(8)),
                    'description' => $n['d'],
                    'district' => $n['dist'],
                    'is_important' => $n['imp'],
                    'status' => 'published',
                    'published_at' => now()->subDays(rand(0, 12)),
                ]
            );
        }
    }

    private function seedEquipment(): void
    {
        $catId = EquipmentCategory::pluck('id', 'slug');
        $userId = User::where('role', 'farmer')->value('id') ?? User::value('id');

        $products = [
            ['name' => 'রোটাভেটর (৬ ফুট)', 'sub' => 'rotavator', 'brand' => 'Sonalika', 'model' => 'RT-180', 'price' => 95000, 'stock' => 3, 'unit' => 'টি', 'cond' => 'new', 'dist' => 'যশোর', 'upz' => 'সদর', 'mob' => '01711000031', 'desc' => '৬ ফুট রোটাভেটর, ট্রাক্টরের সাথে সংযুক্ত করে দ্রুত জমি চাষ ও মই দেওয়া যায়।', 'feat' => true],
            ['name' => 'কম্বাইন হারভেস্টার', 'sub' => 'combine-harvester', 'brand' => 'DAEDONG', 'model' => 'DXM-120', 'price' => 2850000, 'stock' => 1, 'unit' => 'টি', 'cond' => 'new', 'dist' => 'বগুড়া', 'upz' => 'শাজাহানপুর', 'mob' => '01711000032', 'desc' => 'ধান ও গম একসাথে কাটা, মাড়াই ও ঝাড়াই করে। সরকারি ভর্তুকিতে কেনার সুযোগ আছে।', 'feat' => true],
            ['name' => 'রিপার মেশিন', 'sub' => 'reaper', 'brand' => 'Bobcat', 'model' => 'RP-120', 'price' => 78000, 'stock' => 4, 'unit' => 'টি', 'cond' => 'new', 'dist' => 'রংপুর', 'upz' => 'গঙ্গাচড়া', 'mob' => '01711000033', 'desc' => 'ধান ও গম দ্রুত কাটার জন্য হাঁটার পেছনে চালিত রিপার মেশিন।', 'feat' => false],
            ['name' => 'BRRI ধান৮৯ বীজ', 'sub' => 'brri-rice', 'brand' => 'BADC', 'model' => 'BR-89', 'price' => 70, 'stock' => 400, 'unit' => 'কেজি', 'cond' => null, 'dist' => 'দিনাজপুর', 'upz' => 'সদর', 'mob' => '01711000034', 'desc' => 'উচ্চফলনশীল বোরো জাত, রোগ সহনশীল ও ভালো ফলন দেয়।', 'feat' => false],
            ['name' => 'বারি গম৩৩ বীজ', 'sub' => 'wheat-seed', 'brand' => 'BADC', 'model' => 'BG-33', 'price' => 55, 'stock' => 300, 'unit' => 'কেজি', 'cond' => null, 'dist' => 'রাজশাহী', 'upz' => 'গোদাগাড়ী', 'mob' => '01711000035', 'desc' => 'ব্লাস্ট রোগ প্রতিরোধী উন্নত গমের বীজ।', 'feat' => false],
            ['name' => 'এমওপি (পটাশ) সার', 'sub' => 'mop', 'brand' => 'BCIC', 'model' => null, 'price' => 1400, 'stock' => 200, 'unit' => 'বস্তা', 'cond' => null, 'dist' => 'ঢাকা', 'upz' => 'সাভার', 'mob' => '01711000036', 'desc' => 'মিউরেট অব পটাশ, ৫০ কেজি বস্তা — ফসলের রোগ প্রতিরোধ ক্ষমতা বাড়ায়।', 'feat' => false],
            ['name' => 'টিএসপি সার', 'sub' => 'tsp', 'brand' => 'BCIC', 'model' => null, 'price' => 1350, 'stock' => 220, 'unit' => 'বস্তা', 'cond' => null, 'dist' => 'ময়মনসিংহ', 'upz' => 'সদর', 'mob' => '01711000037', 'desc' => 'ট্রিপল সুপার ফসফেট, শিকড়ের বৃদ্ধিতে সহায়ক — ৫০ কেজি বস্তা।', 'feat' => false],
            ['name' => 'ছত্রাকনাশক (Nativo)', 'sub' => 'fungicide', 'brand' => 'Bayer', 'model' => 'Nativo 75WG', 'price' => 560, 'stock' => 95, 'unit' => 'প্যাকেট', 'cond' => null, 'dist' => 'বগুড়া', 'upz' => 'সদর', 'mob' => '01711000038', 'desc' => 'ব্লাস্ট ও খোলপোড়াসহ বিভিন্ন ছত্রাকজনিত রোগ দমনে কার্যকর।', 'feat' => false],
            ['name' => 'পাওয়ার স্প্রেয়ার', 'sub' => 'power-sprayer', 'brand' => 'Kisankraft', 'model' => 'KK-PS22', 'price' => 8500, 'stock' => 15, 'unit' => 'টি', 'cond' => 'new', 'dist' => 'কুমিল্লা', 'upz' => 'দেবিদ্বার', 'mob' => '01711000039', 'desc' => '২২ লিটার ক্ষমতার পেট্রোল ইঞ্জিনচালিত পাওয়ার স্প্রেয়ার।', 'feat' => false],
            ['name' => 'পিভিসি পাইপ (৩ ইঞ্চি)', 'sub' => 'pvc-pipe', 'brand' => 'RFL', 'model' => '3-inch', 'price' => 450, 'stock' => 120, 'unit' => 'পিস', 'cond' => 'new', 'dist' => 'খুলনা', 'upz' => 'ডুমুরিয়া', 'mob' => '01711000040', 'desc' => 'সেচের পানি সরবরাহের জন্য টেকসই ৩ ইঞ্চি পিভিসি পাইপ।', 'feat' => false],
            ['name' => 'গরুর দানাদার খাদ্য', 'sub' => 'cattle-feed', 'brand' => 'ACI', 'model' => 'Dairy Feed', 'price' => 1250, 'stock' => 150, 'unit' => 'বস্তা', 'cond' => null, 'dist' => 'পাবনা', 'upz' => 'সদর', 'mob' => '01711000041', 'desc' => 'দুধের গাভীর জন্য পুষ্টিসমৃদ্ধ দানাদার খাদ্য — ২৫ কেজি বস্তা।', 'feat' => false],
            ['name' => 'ব্রয়লার মুরগির খাদ্য', 'sub' => 'poultry-feed', 'brand' => 'Kazi Farms', 'model' => 'Broiler Starter', 'price' => 2350, 'stock' => 100, 'unit' => 'বস্তা', 'cond' => null, 'dist' => 'গাজীপুর', 'upz' => 'কালিয়াকৈর', 'mob' => '01711000042', 'desc' => 'ব্রয়লার মুরগির দ্রুত বৃদ্ধির জন্য স্টার্টার ফিড — ৫০ কেজি বস্তা।', 'feat' => false],
            ['name' => 'হারভেস্টার ভাড়া সার্ভিস', 'sub' => 'harvester-rental', 'brand' => null, 'model' => null, 'price' => 1500, 'stock' => null, 'unit' => 'বিঘা', 'cond' => null, 'dist' => 'বগুড়া', 'upz' => 'শেরপুর', 'mob' => '01711000043', 'desc' => 'কম্বাইন হারভেস্টার দিয়ে ধান কাটা-মাড়াই সার্ভিস, বিঘা হিসেবে।', 'feat' => false],
        ];

        foreach ($products as $p) {
            EquipmentProduct::updateOrCreate(
                ['name' => $p['name']],
                [
                    'user_id' => $userId,
                    'category_id' => $catId[$p['sub']] ?? null,
                    'brand' => $p['brand'] ?? null,
                    'model' => $p['model'] ?? null,
                    'price' => $p['price'],
                    'stock_quantity' => $p['stock'] ?? null,
                    'unit' => $p['unit'] ?? null,
                    'condition' => $p['cond'] ?? null,
                    'location' => $p['dist'],
                    'upazila' => $p['upz'] ?? null,
                    'mobile' => $p['mob'],
                    'whatsapp' => $p['mob'],
                    'description' => $p['desc'] ?? null,
                    'image' => null,
                    'status' => 'active',
                    'approved' => true,
                    'featured' => $p['feat'] ?? false,
                ]
            );
        }
    }
}
