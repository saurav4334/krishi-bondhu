<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\EquipmentProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * 60+ demo products for the কৃষি সরঞ্জাম marketplace.
 *
 * Safe & idempotent: deletes ONLY its own demo rows (is_demo = true) before
 * re-seeding, so real/manually-added products are never touched. Each product
 * references a real category image in public/images/equipment/ — these are
 * served directly (and via Media::url) with NO storage-symlink dependency.
 */
class EquipmentProductSeeder extends Seeder
{
    public function run(): void
    {
        // Wipe only previously-generated demo products (keep real ones).
        EquipmentProduct::where('is_demo', true)->delete();

        $catId = EquipmentCategory::pluck('id', 'slug');
        $userId = User::where('role', 'farmer')->value('id') ?? User::value('id');
        $districts = [
            ['ঢাকা', 'সাভার'], ['বগুড়া', 'শেরপুর'], ['রংপুর', 'মিঠাপুকুর'], ['রাজশাহী', 'পবা'],
            ['ময়মনসিংহ', 'ত্রিশাল'], ['যশোর', 'অভয়নগর'], ['কুমিল্লা', 'দেবিদ্বার'], ['দিনাজপুর', 'বীরগঞ্জ'],
            ['পাবনা', 'সদর'], ['খুলনা', 'ডুমুরিয়া'], ['টাঙ্গাইল', 'ঘাটাইল'], ['জামালপুর', 'সদর'],
        ];

        $i = 0;
        foreach ($this->products() as $p) {
            [$dist, $upz] = $districts[$i % count($districts)];
            $mobile = '017300' . str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT);
            $i++;

            EquipmentProduct::updateOrCreate(
                ['name' => $p['name'], 'is_demo' => true],
                [
                    'user_id' => $userId,
                    'category_id' => $catId[$p['sub']] ?? null,
                    'brand' => $p['brand'] ?? null,
                    'model' => $p['model'] ?? null,
                    'price' => $p['price'],
                    'stock_quantity' => $p['stock'] ?? null,
                    'unit' => $p['unit'] ?? null,
                    'condition' => $p['cond'] ?? null,
                    'location' => $dist,
                    'upazila' => $upz,
                    'mobile' => $mobile,
                    'whatsapp' => $mobile,
                    'description' => $p['desc'],
                    'image' => 'images/equipment/' . $p['img'] . '.jpg',
                    'status' => 'active',
                    'approved' => true,
                    'featured' => $p['feat'] ?? false,
                ]
            );
        }
    }

    /** name, sub(category slug), img(file), brand, model, price, stock, unit, cond, feat, desc */
    private function products(): array
    {
        return [
            // ---- কৃষি যন্ত্রপাতি (12) ----
            ['name' => 'মাহিন্দ্রা ট্রাক্টর ৫৭৫', 'sub' => 'tractor', 'img' => 'tractor', 'brand' => 'Mahindra', 'model' => '575 DI', 'price' => 920000, 'stock' => 2, 'unit' => 'টি', 'cond' => 'new', 'feat' => true, 'desc' => '৪৭ এইচপি শক্তিশালী ট্রাক্টর, সব ধরনের জমিতে ব্যবহারযোগ্য।'],
            ['name' => 'সোনালিকা ট্রাক্টর DI 745', 'sub' => 'tractor', 'img' => 'tractor', 'brand' => 'Sonalika', 'model' => 'DI-745', 'price' => 880000, 'stock' => 2, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => '৫০ এইচপি ট্রাক্টর, কম জ্বালানি খরচে বেশি কাজ।'],
            ['name' => 'Yanmar পাওয়ার টিলার', 'sub' => 'power-tiller', 'img' => 'power-tiller', 'brand' => 'Yanmar', 'model' => 'YZC-12', 'price' => 165000, 'stock' => 5, 'unit' => 'টি', 'cond' => 'new', 'feat' => true, 'desc' => '১২ এইচপি ডিজেল পাওয়ার টিলার, ১ বছরের ওয়ারেন্টি।'],
            ['name' => 'ACI পাওয়ার টিলার', 'sub' => 'power-tiller', 'img' => 'power-tiller', 'brand' => 'ACI', 'model' => 'DF-15L', 'price' => 158000, 'stock' => 4, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => '১৫ এইচপি পাওয়ার টিলার, টেকসই ও সাশ্রয়ী।'],
            ['name' => 'ACI মিনি ট্রাক্টর', 'sub' => 'mini-tractor', 'img' => 'tractor', 'brand' => 'ACI', 'model' => 'AT-254', 'price' => 480000, 'stock' => 2, 'unit' => 'টি', 'cond' => 'new', 'feat' => true, 'desc' => '২৫ এইচপি ৪ চাকার মিনি ট্রাক্টর।'],
            ['name' => 'DAEDONG কম্বাইন হারভেস্টার', 'sub' => 'combine-harvester', 'img' => 'harvester', 'brand' => 'DAEDONG', 'model' => 'DXM-120', 'price' => 2850000, 'stock' => 1, 'unit' => 'টি', 'cond' => 'new', 'feat' => true, 'desc' => 'ধান-গম একসাথে কাটা, মাড়াই ও ঝাড়াই; সরকারি ভর্তুকিতে কেনার সুযোগ।'],
            ['name' => 'হারভেস্টার (রিপার বাইন্ডার)', 'sub' => 'harvester', 'img' => 'harvester', 'brand' => 'World', 'model' => 'RB-120', 'price' => 145000, 'stock' => 3, 'unit' => 'টি', 'cond' => 'new', 'feat' => true, 'desc' => 'ধান-গম কেটে আঁটি বেঁধে দেয়, শ্রম সাশ্রয়ী।'],
            ['name' => 'ধান মাড়াই মেশিন', 'sub' => 'paddy-thresher', 'img' => 'harvester', 'brand' => 'Janata', 'model' => 'JT-500', 'price' => 45000, 'stock' => 8, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => 'উচ্চ ক্ষমতার ধান মাড়াই মেশিন, ঘণ্টায় ৫০০ কেজি।'],
            ['name' => 'গম মাড়াই মেশিন', 'sub' => 'wheat-thresher', 'img' => 'harvester', 'brand' => 'Alim', 'model' => 'WT-400', 'price' => 42000, 'stock' => 5, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => 'গম মাড়াইয়ের জন্য কার্যকর মেশিন।'],
            ['name' => 'রোটাভেটর (৬ ফুট)', 'sub' => 'rotavator', 'img' => 'tractor', 'brand' => 'Sonalika', 'model' => 'RT-180', 'price' => 95000, 'stock' => 4, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => 'ট্রাক্টরের সাথে সংযুক্ত করে দ্রুত জমি চাষ ও মই।'],
            ['name' => 'সিড ড্রিল মেশিন', 'sub' => 'seed-drill', 'img' => 'tractor', 'brand' => 'BARI', 'model' => 'SD-9', 'price' => 68000, 'stock' => 3, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => 'সারিবদ্ধভাবে বীজ ও সার একসাথে বপন করে।'],
            ['name' => 'রাইস ট্রান্সপ্লান্টার', 'sub' => 'rice-transplanter', 'img' => 'tractor', 'brand' => 'Kubota', 'model' => 'SPW-48C', 'price' => 320000, 'stock' => 2, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => 'ধানের চারা দ্রুত ও সমানভাবে রোপণ করে।'],

            // ---- সেচ ও স্প্রে (8) ----
            ['name' => 'ডিজেল সেচ পাম্প (৩")', 'sub' => 'diesel-pump', 'img' => 'pump', 'brand' => 'Walton', 'model' => 'WDP-3', 'price' => 18000, 'stock' => 12, 'unit' => 'টি', 'cond' => 'new', 'feat' => true, 'desc' => '৩ ইঞ্চি ডিজেল সেচ পাম্প, দ্রুত পানি সরবরাহ।'],
            ['name' => 'ইলেকট্রিক সেচ পাম্প', 'sub' => 'electric-pump', 'img' => 'pump', 'brand' => 'RFL', 'model' => 'EP-2', 'price' => 9500, 'stock' => 15, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => '২ ইঞ্চি বৈদ্যুতিক সেচ পাম্প, কম খরচে সেচ।'],
            ['name' => 'সাবমারসিবল পাম্প', 'sub' => 'submersible-pump', 'img' => 'pump', 'brand' => 'Pedrollo', 'model' => 'SP-4', 'price' => 28000, 'stock' => 6, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => 'গভীর নলকূপের জন্য সাবমারসিবল পাম্প।'],
            ['name' => 'ব্যাটারি স্প্রে মেশিন', 'sub' => 'battery-sprayer', 'img' => 'sprayer', 'brand' => 'Matabi', 'model' => 'BS-16', 'price' => 3500, 'stock' => 25, 'unit' => 'টি', 'cond' => 'new', 'feat' => true, 'desc' => '১৬ লিটার রিচার্জেবল ব্যাটারি স্প্রে মেশিন।'],
            ['name' => 'পাওয়ার স্প্রেয়ার', 'sub' => 'power-sprayer', 'img' => 'sprayer', 'brand' => 'Kisankraft', 'model' => 'KK-PS22', 'price' => 8500, 'stock' => 14, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => '২২ লিটার পেট্রোল ইঞ্জিনচালিত পাওয়ার স্প্রেয়ার।'],
            ['name' => 'হ্যান্ড স্প্রেয়ার (৫ লি.)', 'sub' => 'hand-sprayer', 'img' => 'sprayer', 'brand' => 'Matabi', 'model' => 'HS-5', 'price' => 1200, 'stock' => 40, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => '৫ লিটার হ্যান্ড স্প্রেয়ার, সহজ ব্যবহারযোগ্য।'],
            ['name' => 'ড্রিপ ইরিগেশন কিট', 'sub' => 'drip-irrigation', 'img' => 'pump', 'brand' => 'Jain', 'model' => 'DK-100', 'price' => 6500, 'stock' => 10, 'unit' => 'সেট', 'cond' => 'new', 'feat' => false, 'desc' => 'সবজি ও ফল বাগানের জন্য পানি-সাশ্রয়ী ড্রিপ সেচ কিট।'],
            ['name' => 'স্প্রিংকলার সেট', 'sub' => 'sprinkler', 'img' => 'pump', 'brand' => 'Rain Bird', 'model' => 'SP-25', 'price' => 4200, 'stock' => 12, 'unit' => 'সেট', 'cond' => 'new', 'feat' => false, 'desc' => 'সমানভাবে পানি ছিটানোর স্প্রিংকলার সেচ ব্যবস্থা।'],

            // ---- বীজ (8) ----
            ['name' => 'BRRI ধান৮৯ বীজ', 'sub' => 'brri-rice', 'img' => 'seeds', 'brand' => 'BADC', 'model' => 'BR-89', 'price' => 70, 'stock' => 500, 'unit' => 'কেজি', 'cond' => null, 'feat' => true, 'desc' => 'উচ্চফলনশীল বোরো জাত, রোগ সহনশীল।'],
            ['name' => 'BRRI ধান২৮ বীজ', 'sub' => 'brri-rice', 'img' => 'seeds', 'brand' => 'BADC', 'model' => 'BR-28', 'price' => 65, 'stock' => 600, 'unit' => 'কেজি', 'cond' => null, 'feat' => false, 'desc' => 'জনপ্রিয় বোরো জাত, অঙ্কুরোদগম হার ৯৫%।'],
            ['name' => 'হাইব্রিড ভুট্টা বীজ', 'sub' => 'hybrid-maize', 'img' => 'seeds', 'brand' => 'Lal Teer', 'model' => 'NK-40', 'price' => 450, 'stock' => 200, 'unit' => 'কেজি', 'cond' => null, 'feat' => true, 'desc' => 'হাইব্রিড ভুট্টা বীজ, উচ্চ ফলনশীল ও রোগ প্রতিরোধী।'],
            ['name' => 'টমেটো হাইব্রিড বীজ', 'sub' => 'tomato-seed', 'img' => 'seeds', 'brand' => 'Lal Teer', 'model' => 'Bahubali', 'price' => 180, 'stock' => 150, 'unit' => 'প্যাকেট', 'cond' => null, 'feat' => false, 'desc' => 'সারা বছর চাষযোগ্য হাইব্রিড টমেটো বীজ।'],
            ['name' => 'মরিচ বীজ (অগ্নি)', 'sub' => 'chili-seed', 'img' => 'seeds', 'brand' => 'Metal', 'model' => 'Agni', 'price' => 220, 'stock' => 120, 'unit' => 'প্যাকেট', 'cond' => null, 'feat' => false, 'desc' => 'ঝাল মরিচের হাইব্রিড বীজ, ভালো ফলন।'],
            ['name' => 'সরিষা বীজ (বারি-১৪)', 'sub' => 'mustard-seed', 'img' => 'seeds', 'brand' => 'BADC', 'model' => 'BARI-14', 'price' => 120, 'stock' => 180, 'unit' => 'কেজি', 'cond' => null, 'feat' => false, 'desc' => 'স্বল্পমেয়াদি উচ্চফলনশীল সরিষা বীজ।'],
            ['name' => 'বেগুন বীজ', 'sub' => 'brinjal-seed', 'img' => 'seeds', 'brand' => 'Lal Teer', 'model' => 'Singnath', 'price' => 160, 'stock' => 100, 'unit' => 'প্যাকেট', 'cond' => null, 'feat' => false, 'desc' => 'লম্বা বেগুনের হাইব্রিড বীজ।'],
            ['name' => 'সবজি বীজ প্যাক', 'sub' => 'cucumber-seed', 'img' => 'seeds', 'brand' => 'Lal Teer', 'model' => 'Mixed', 'price' => 250, 'stock' => 90, 'unit' => 'প্যাকেট', 'cond' => null, 'feat' => false, 'desc' => 'শসা, লাউ, করলাসহ মিশ্র সবজি বীজের প্যাক।'],

            // ---- সার (8) ----
            ['name' => 'ইউরিয়া সার', 'sub' => 'urea', 'img' => 'fertilizer', 'brand' => 'BCIC', 'model' => null, 'price' => 1350, 'stock' => 300, 'unit' => 'বস্তা', 'cond' => null, 'feat' => true, 'desc' => 'মানসম্মত ইউরিয়া সার, ৫০ কেজি বস্তা।'],
            ['name' => 'টিএসপি সার', 'sub' => 'tsp', 'img' => 'fertilizer', 'brand' => 'BCIC', 'model' => null, 'price' => 1350, 'stock' => 250, 'unit' => 'বস্তা', 'cond' => null, 'feat' => false, 'desc' => 'ট্রিপল সুপার ফসফেট, শিকড়ের বৃদ্ধিতে সহায়ক।'],
            ['name' => 'ডিএপি সার', 'sub' => 'dap', 'img' => 'fertilizer', 'brand' => 'BCIC', 'model' => null, 'price' => 1600, 'stock' => 220, 'unit' => 'বস্তা', 'cond' => null, 'feat' => true, 'desc' => 'ডিএপি সার ৫০ কেজি বস্তা, দ্রুত বৃদ্ধিতে সহায়ক।'],
            ['name' => 'এমওপি (পটাশ) সার', 'sub' => 'mop', 'img' => 'fertilizer', 'brand' => 'BCIC', 'model' => null, 'price' => 1400, 'stock' => 200, 'unit' => 'বস্তা', 'cond' => null, 'feat' => false, 'desc' => 'মিউরেট অব পটাশ, রোগ প্রতিরোধ ক্ষমতা বাড়ায়।'],
            ['name' => 'ভার্মি কম্পোস্ট', 'sub' => 'vermicompost', 'img' => 'fertilizer', 'brand' => 'Organic BD', 'model' => null, 'price' => 25, 'stock' => 1000, 'unit' => 'কেজি', 'cond' => null, 'feat' => true, 'desc' => 'কেঁচো সার, মাটির উর্বরতা বৃদ্ধি করে।'],
            ['name' => 'জৈব কম্পোস্ট সার', 'sub' => 'organic-compost', 'img' => 'fertilizer', 'brand' => 'Green Agro', 'model' => null, 'price' => 20, 'stock' => 800, 'unit' => 'কেজি', 'cond' => null, 'feat' => false, 'desc' => 'প্রাকৃতিক জৈব সার, পরিবেশবান্ধব।'],
            ['name' => 'লিকুইড ফার্টিলাইজার', 'sub' => 'liquid-fertilizer', 'img' => 'fertilizer', 'brand' => 'ACI', 'model' => 'GroMore', 'price' => 350, 'stock' => 130, 'unit' => 'বোতল', 'cond' => null, 'feat' => false, 'desc' => 'পাতায় স্প্রে করার তরল সার, দ্রুত কার্যকর।'],
            ['name' => 'মাইক্রোনিউট্রিয়েন্ট সার', 'sub' => 'micronutrient', 'img' => 'fertilizer', 'brand' => 'Auto Crop', 'model' => 'Zinc+', 'price' => 280, 'stock' => 110, 'unit' => 'প্যাকেট', 'cond' => null, 'feat' => false, 'desc' => 'দস্তা ও বোরনসহ অণুখাদ্য সার।'],

            // ---- কৃষি ঔষধ (5) ----
            ['name' => 'কীটনাশক (Imidacloprid)', 'sub' => 'insecticide', 'img' => 'pesticide', 'brand' => 'Syngenta', 'model' => 'Admire', 'price' => 480, 'stock' => 100, 'unit' => 'বোতল', 'cond' => null, 'feat' => true, 'desc' => 'পোকামাকড় দমনে কার্যকর কীটনাশক।'],
            ['name' => 'ফাঙ্গিসাইড (Nativo)', 'sub' => 'fungicide', 'img' => 'pesticide', 'brand' => 'Bayer', 'model' => 'Nativo 75WG', 'price' => 560, 'stock' => 90, 'unit' => 'প্যাকেট', 'cond' => null, 'feat' => false, 'desc' => 'ব্লাস্টসহ ছত্রাকজনিত রোগ দমনে কার্যকর।'],
            ['name' => 'আগাছানাশক (Whip)', 'sub' => 'herbicide', 'img' => 'pesticide', 'brand' => 'ACI', 'model' => 'Whip Super', 'price' => 600, 'stock' => 80, 'unit' => 'বোতল', 'cond' => null, 'feat' => false, 'desc' => 'আগাছা দমনে কার্যকর হার্বিসাইড।'],
            ['name' => 'জৈব বালাইনাশক', 'sub' => 'bio-pesticide', 'img' => 'pesticide', 'brand' => 'Ispahani', 'model' => 'BioNeem', 'price' => 320, 'stock' => 70, 'unit' => 'বোতল', 'cond' => null, 'feat' => false, 'desc' => 'নিম-ভিত্তিক পরিবেশবান্ধব জৈব বালাইনাশক।'],
            ['name' => 'ইঁদুর নিয়ন্ত্রণ ঔষধ', 'sub' => 'rodent-control', 'img' => 'pesticide', 'brand' => 'Bushra', 'model' => 'RatKill', 'price' => 150, 'stock' => 60, 'unit' => 'প্যাকেট', 'cond' => null, 'feat' => false, 'desc' => 'ফসলের ইঁদুর দমনে কার্যকর বিষটোপ।'],

            // ---- কৃষি সরঞ্জাম / tools (8) ----
            ['name' => 'কোদাল', 'sub' => 'kodal', 'img' => 'tools', 'brand' => 'দেশি', 'model' => null, 'price' => 350, 'stock' => 60, 'unit' => 'টি', 'cond' => 'new', 'feat' => true, 'desc' => 'উন্নত মানের লোহার কোদাল, টেকসই হাতল।'],
            ['name' => 'কাস্তে', 'sub' => 'kaste', 'img' => 'tools', 'brand' => 'দেশি', 'model' => null, 'price' => 180, 'stock' => 70, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => 'ধারালো কাস্তে, ফসল কাটার জন্য উপযোগী।'],
            ['name' => 'বেলচা', 'sub' => 'shovel', 'img' => 'tools', 'brand' => 'দেশি', 'model' => null, 'price' => 320, 'stock' => 50, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => 'মাটি কাটা ও সরানোর জন্য মজবুত বেলচা।'],
            ['name' => 'কুড়াল', 'sub' => 'axe', 'img' => 'tools', 'brand' => 'দেশি', 'model' => null, 'price' => 420, 'stock' => 40, 'unit' => 'টি', 'cond' => 'new', 'feat' => false, 'desc' => 'কাঠ ও ডাল কাটার ধারালো কুড়াল।'],
            ['name' => 'কৃষি গ্লাভস', 'sub' => 'gloves', 'img' => 'tools', 'brand' => 'Safety', 'model' => null, 'price' => 120, 'stock' => 100, 'unit' => 'জোড়া', 'cond' => 'new', 'feat' => false, 'desc' => 'কীটনাশক ও কাজের সময় হাত সুরক্ষায় গ্লাভস।'],
            ['name' => 'গামবুট', 'sub' => 'gumboot', 'img' => 'tools', 'brand' => 'RFL', 'model' => null, 'price' => 450, 'stock' => 80, 'unit' => 'জোড়া', 'cond' => 'new', 'feat' => false, 'desc' => 'কাদা-পানিতে কাজের জন্য টেকসই গামবুট।'],
            ['name' => 'হোস পাইপ (৫০ ফুট)', 'sub' => 'hose-pipe', 'img' => 'tools', 'brand' => 'RFL', 'model' => '50ft', 'price' => 750, 'stock' => 60, 'unit' => 'রোল', 'cond' => 'new', 'feat' => false, 'desc' => 'সেচ ও পানি সরবরাহের নমনীয় হোস পাইপ।'],
            ['name' => 'পিভিসি পাইপ (৩")', 'sub' => 'pvc-pipe', 'img' => 'tools', 'brand' => 'RFL', 'model' => '3-inch', 'price' => 450, 'stock' => 120, 'unit' => 'পিস', 'cond' => 'new', 'feat' => false, 'desc' => 'সেচের পানি সরবরাহের টেকসই পিভিসি পাইপ।'],

            // ---- প্রাণিসম্পদ ও পোল্ট্রি (6) ----
            ['name' => 'গরুর দানাদার খাদ্য', 'sub' => 'cattle-feed', 'img' => 'livestock', 'brand' => 'ACI', 'model' => 'Dairy', 'price' => 1250, 'stock' => 150, 'unit' => 'বস্তা', 'cond' => null, 'feat' => true, 'desc' => 'দুধের গাভীর জন্য পুষ্টিসমৃদ্ধ দানাদার খাদ্য।'],
            ['name' => 'ছাগলের খাদ্য', 'sub' => 'goat-feed', 'img' => 'livestock', 'brand' => 'Nourish', 'model' => 'GoatGrow', 'price' => 1100, 'stock' => 120, 'unit' => 'বস্তা', 'cond' => null, 'feat' => false, 'desc' => 'ছাগলের দ্রুত বৃদ্ধির জন্য সুষম খাদ্য।'],
            ['name' => 'ব্রয়লার মুরগির খাদ্য', 'sub' => 'poultry-feed', 'img' => 'livestock', 'brand' => 'Kazi Farms', 'model' => 'Broiler Starter', 'price' => 2350, 'stock' => 100, 'unit' => 'বস্তা', 'cond' => null, 'feat' => false, 'desc' => 'ব্রয়লার মুরগির স্টার্টার ফিড, ৫০ কেজি বস্তা।'],
            ['name' => 'ব্রয়লার মুরগির বাচ্চা', 'sub' => 'broiler', 'img' => 'livestock', 'brand' => 'Kazi Farms', 'model' => 'Day-old', 'price' => 55, 'stock' => 1000, 'unit' => 'পিস', 'cond' => null, 'feat' => false, 'desc' => 'সুস্থ একদিন বয়সী ব্রয়লার বাচ্চা।'],
            ['name' => 'মাছের খাদ্য', 'sub' => 'fish-feed', 'img' => 'livestock', 'brand' => 'Mega', 'model' => 'Floating', 'price' => 1450, 'stock' => 90, 'unit' => 'বস্তা', 'cond' => null, 'feat' => false, 'desc' => 'ভাসমান মাছের খাদ্য, দ্রুত বৃদ্ধিতে সহায়ক।'],
            ['name' => 'পুকুরের ওষুধ', 'sub' => 'pond-medicine', 'img' => 'livestock', 'brand' => 'Fishtech', 'model' => 'AquaCure', 'price' => 380, 'stock' => 70, 'unit' => 'প্যাকেট', 'cond' => null, 'feat' => false, 'desc' => 'পুকুরের পানি ও মাছের রোগ নিয়ন্ত্রণে কার্যকর।'],

            // ---- কৃষি সেবা (5) ----
            ['name' => 'ট্রাক্টর ভাড়া সার্ভিস', 'sub' => 'tractor-rental', 'img' => 'service', 'brand' => null, 'model' => null, 'price' => 1200, 'stock' => null, 'unit' => 'ঘন্টা', 'cond' => null, 'feat' => true, 'desc' => 'জমি চাষের জন্য ট্রাক্টর ভাড়া, ঘণ্টা হিসেবে।'],
            ['name' => 'হারভেস্টার ভাড়া সার্ভিস', 'sub' => 'harvester-rental', 'img' => 'service', 'brand' => null, 'model' => null, 'price' => 1500, 'stock' => null, 'unit' => 'বিঘা', 'cond' => null, 'feat' => false, 'desc' => 'কম্বাইন হারভেস্টারে ধান কাটা-মাড়াই, বিঘা হিসেবে।'],
            ['name' => 'সেচ পাম্প ভাড়া সার্ভিস', 'sub' => 'pump-rental', 'img' => 'service', 'brand' => null, 'model' => null, 'price' => 800, 'stock' => null, 'unit' => 'দিন', 'cond' => null, 'feat' => false, 'desc' => 'সেচের জন্য পাম্প ভাড়া, দৈনিক হিসেবে।'],
            ['name' => 'স্প্রে সার্ভিস (ড্রোন)', 'sub' => 'spray-service', 'img' => 'service', 'brand' => null, 'model' => null, 'price' => 400, 'stock' => null, 'unit' => 'বিঘা', 'cond' => null, 'feat' => false, 'desc' => 'ড্রোন/মেশিনে কীটনাশক স্প্রে সার্ভিস, বিঘা হিসেবে।'],
            ['name' => 'কৃষি পণ্য পরিবহন সেবা', 'sub' => 'transport-service', 'img' => 'service', 'brand' => null, 'model' => null, 'price' => 25, 'stock' => null, 'unit' => 'কিমি', 'cond' => null, 'feat' => false, 'desc' => 'ফসল ও কৃষি পণ্য পরিবহনের সেবা, কিমি হিসেবে।'],
        ];
    }
}
