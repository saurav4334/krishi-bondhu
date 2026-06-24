<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\EquipmentProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo products for the কৃষি সরঞ্জাম marketplace (testing / showcase).
 *
 * Idempotent: updateOrCreate keyed by product name, so re-running never
 * duplicates. category_id points to the leaf subcategory (matching how the
 * create form stores it). All rows are active + approved so they show on
 * /equipment immediately; a few are featured. Independent of ফসল বিক্রয়.
 */
class EquipmentProductSeeder extends Seeder
{
    public function run(): void
    {
        // Self-sufficient: ensure the taxonomy exists even if run standalone.
        if (EquipmentCategory::whereNotNull('parent_id')->count() === 0) {
            $this->call(EquipmentCategorySeeder::class);
        }

        $catId = EquipmentCategory::pluck('id', 'slug');           // slug => id
        $userId = User::where('role', 'farmer')->value('id') ?? User::value('id');

        foreach ($this->products() as $p) {
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
                    'condition' => $p['condition'] ?? null,
                    'location' => $p['district'],
                    'upazila' => $p['upazila'] ?? null,
                    'mobile' => $p['mobile'],
                    'whatsapp' => $p['mobile'],
                    'description' => $p['desc'] ?? null,
                    'image' => null, // UI falls back to the category icon when null
                    'status' => 'active',
                    'approved' => true,
                    'featured' => $p['featured'] ?? false,
                ]
            );
        }
    }

    private function products(): array
    {
        return [
            // --- কৃষি যন্ত্রপাতি ---
            ['name' => 'Yanmar পাওয়ার টিলার', 'sub' => 'power-tiller', 'brand' => 'Yanmar', 'model' => 'YZC-12', 'price' => 165000, 'stock' => 4, 'unit' => 'টি', 'condition' => 'new', 'district' => 'বগুড়া', 'upazila' => 'শেরপুর', 'mobile' => '01711000001', 'desc' => '১২ এইচপি ডিজেল পাওয়ার টিলার, কম জ্বালানি খরচ, ১ বছরের ওয়ারেন্টি।', 'featured' => true],
            ['name' => 'ACI মিনি ট্রাক্টর', 'sub' => 'mini-tractor', 'brand' => 'ACI', 'model' => 'AT-254', 'price' => 480000, 'stock' => 2, 'unit' => 'টি', 'condition' => 'new', 'district' => 'রংপুর', 'upazila' => 'মিঠাপুকুর', 'mobile' => '01711000002', 'desc' => '২৫ এইচপি ৪ চাকার মিনি ট্রাক্টর, সব ধরনের জমিতে ব্যবহারযোগ্য।', 'featured' => true],
            ['name' => 'ধান মাড়াই মেশিন', 'sub' => 'paddy-thresher', 'brand' => 'Janata', 'model' => 'JT-500', 'price' => 45000, 'stock' => 6, 'unit' => 'টি', 'condition' => 'new', 'district' => 'ময়মনসিংহ', 'upazila' => 'ত্রিশাল', 'mobile' => '01711000003', 'desc' => 'উচ্চ ক্ষমতার ধান মাড়াই মেশিন, ঘণ্টায় ৫০০ কেজি।', 'featured' => false],
            ['name' => 'সেচ পাম্প (ডিজেল)', 'sub' => 'diesel-pump', 'brand' => 'Walton', 'model' => 'WDP-3', 'price' => 18000, 'stock' => 10, 'unit' => 'টি', 'condition' => 'new', 'district' => 'রাজশাহী', 'upazila' => 'পবা', 'mobile' => '01711000004', 'desc' => '৩ ইঞ্চি ডিজেল সেচ পাম্প, দ্রুত পানি সরবরাহ।', 'featured' => false],
            ['name' => 'ব্যাটারি স্প্রে মেশিন', 'sub' => 'battery-sprayer', 'brand' => 'Matabi', 'model' => 'BS-16', 'price' => 3500, 'stock' => 25, 'unit' => 'টি', 'condition' => 'new', 'district' => 'যশোর', 'upazila' => 'অভয়নগর', 'mobile' => '01711000005', 'desc' => '১৬ লিটার রিচার্জেবল ব্যাটারি স্প্রে মেশিন।', 'featured' => false],

            // --- বীজ ---
            ['name' => 'BRRI ধান২৮ বীজ', 'sub' => 'brri-rice', 'brand' => 'BRRI', 'model' => 'BR-28', 'price' => 65, 'stock' => 500, 'unit' => 'কেজি', 'condition' => null, 'district' => 'বগুড়া', 'upazila' => 'সদর', 'mobile' => '01711000006', 'desc' => 'উচ্চ ফলনশীল BRRI ধান২৮ বীজ, অঙ্কুরোদগম হার ৯৫%।', 'featured' => true],
            ['name' => 'হাইব্রিড ভুট্টা বীজ', 'sub' => 'hybrid-maize', 'brand' => 'Lal Teer', 'model' => 'NK-40', 'price' => 450, 'stock' => 200, 'unit' => 'কেজি', 'condition' => null, 'district' => 'দিনাজপুর', 'upazila' => 'বীরগঞ্জ', 'mobile' => '01711000007', 'desc' => 'হাইব্রিড ভুট্টা বীজ, রোগ প্রতিরোধী ও উচ্চ ফলনশীল।', 'featured' => false],
            ['name' => 'টমেটো বীজ', 'sub' => 'tomato-seed', 'brand' => 'Lal Teer', 'model' => 'Bahubali', 'price' => 180, 'stock' => 150, 'unit' => 'প্যাকেট', 'condition' => null, 'district' => 'যশোর', 'upazila' => 'সদর', 'mobile' => '01711000008', 'desc' => 'হাইব্রিড টমেটো বীজ, সারা বছর চাষযোগ্য।', 'featured' => false],
            ['name' => 'মরিচ বীজ', 'sub' => 'chili-seed', 'brand' => 'Metal', 'model' => 'Agni', 'price' => 220, 'stock' => 120, 'unit' => 'প্যাকেট', 'condition' => null, 'district' => 'কুমিল্লা', 'upazila' => 'দেবিদ্বার', 'mobile' => '01711000009', 'desc' => 'ঝাল মরিচের হাইব্রিড বীজ, ভালো ফলন।', 'featured' => false],

            // --- সার ---
            ['name' => 'ইউরিয়া সার', 'sub' => 'urea', 'brand' => 'BCIC', 'model' => null, 'price' => 1350, 'stock' => 300, 'unit' => 'বস্তা', 'condition' => null, 'district' => 'ঢাকা', 'upazila' => 'সাভার', 'mobile' => '01711000010', 'desc' => 'মানসম্মত ইউরিয়া সার, ৫০ কেজি বস্তা।', 'featured' => true],
            ['name' => 'ডিএপি সার', 'sub' => 'dap', 'brand' => 'BCIC', 'model' => null, 'price' => 1600, 'stock' => 250, 'unit' => 'বস্তা', 'condition' => null, 'district' => 'রাজশাহী', 'upazila' => 'সদর', 'mobile' => '01711000011', 'desc' => 'ডিএপি সার ৫০ কেজি বস্তা, ফসলের দ্রুত বৃদ্ধিতে সহায়ক।', 'featured' => false],
            ['name' => 'ভার্মি কম্পোস্ট', 'sub' => 'vermicompost', 'brand' => 'Organic BD', 'model' => null, 'price' => 25, 'stock' => 1000, 'unit' => 'কেজি', 'condition' => null, 'district' => 'ময়মনসিংহ', 'upazila' => 'সদর', 'mobile' => '01711000012', 'desc' => 'কেঁচো সার (ভার্মি কম্পোস্ট), মাটির উর্বরতা বৃদ্ধি করে।', 'featured' => false],
            ['name' => 'জৈব সার', 'sub' => 'organic-compost', 'brand' => 'Green Agro', 'model' => null, 'price' => 20, 'stock' => 800, 'unit' => 'কেজি', 'condition' => null, 'district' => 'রংপুর', 'upazila' => 'সদর', 'mobile' => '01711000013', 'desc' => 'প্রাকৃতিক জৈব সার, পরিবেশবান্ধব ও নিরাপদ।', 'featured' => false],

            // --- কৃষি ঔষধ ---
            ['name' => 'কীটনাশক (Imidacloprid)', 'sub' => 'insecticide', 'brand' => 'Syngenta', 'model' => 'Admire', 'price' => 480, 'stock' => 100, 'unit' => 'বোতল', 'condition' => null, 'district' => 'কুমিল্লা', 'upazila' => 'সদর', 'mobile' => '01711000014', 'desc' => 'কার্যকর কীটনাশক, পোকামাকড় দমনে অত্যন্ত উপযোগী।', 'featured' => false],
            ['name' => 'ফাঙ্গিসাইড', 'sub' => 'fungicide', 'brand' => 'Bayer', 'model' => 'Nativo', 'price' => 550, 'stock' => 90, 'unit' => 'বোতল', 'condition' => null, 'district' => 'বগুড়া', 'upazila' => 'সদর', 'mobile' => '01711000015', 'desc' => 'ছত্রাকনাশক, ফসলের ছত্রাকজনিত রোগ প্রতিরোধে কার্যকর।', 'featured' => false],
            ['name' => 'আগাছানাশক', 'sub' => 'herbicide', 'brand' => 'ACI', 'model' => 'Whip', 'price' => 600, 'stock' => 80, 'unit' => 'বোতল', 'condition' => null, 'district' => 'রাজশাহী', 'upazila' => 'সদর', 'mobile' => '01711000016', 'desc' => 'আগাছা দমনে কার্যকর হার্বিসাইড।', 'featured' => false],

            // --- কৃষি সরঞ্জাম ---
            ['name' => 'কোদাল', 'sub' => 'kodal', 'brand' => 'দেশি', 'model' => null, 'price' => 350, 'stock' => 60, 'unit' => 'টি', 'condition' => 'new', 'district' => 'ঢাকা', 'upazila' => 'ধামরাই', 'mobile' => '01711000017', 'desc' => 'উন্নত মানের লোহার কোদাল, টেকসই হাতল।', 'featured' => false],
            ['name' => 'কাস্তে', 'sub' => 'kaste', 'brand' => 'দেশি', 'model' => null, 'price' => 180, 'stock' => 70, 'unit' => 'টি', 'condition' => 'new', 'district' => 'ময়মনসিংহ', 'upazila' => 'সদর', 'mobile' => '01711000018', 'desc' => 'ধারালো কাস্তে, ফসল কাটার জন্য উপযোগী।', 'featured' => false],
            ['name' => 'হ্যান্ড স্প্রেয়ার', 'sub' => 'hand-sprayer', 'brand' => 'Matabi', 'model' => 'HS-5', 'price' => 1200, 'stock' => 40, 'unit' => 'টি', 'condition' => 'new', 'district' => 'যশোর', 'upazila' => 'সদর', 'mobile' => '01711000019', 'desc' => '৫ লিটার হ্যান্ড স্প্রেয়ার, সহজ ব্যবহারযোগ্য।', 'featured' => true],

            // --- কৃষি সেবা ---
            ['name' => 'ট্রাক্টর ভাড়া সার্ভিস', 'sub' => 'tractor-rental', 'brand' => null, 'model' => null, 'price' => 1200, 'stock' => null, 'unit' => 'ঘন্টা', 'condition' => null, 'district' => 'রংপুর', 'upazila' => 'মিঠাপুকুর', 'mobile' => '01711000020', 'desc' => 'জমি চাষের জন্য ট্রাক্টর ভাড়া, ঘণ্টা হিসেবে।', 'featured' => false],
            ['name' => 'সেচ পাম্প ভাড়া সার্ভিস', 'sub' => 'pump-rental', 'brand' => null, 'model' => null, 'price' => 800, 'stock' => null, 'unit' => 'দিন', 'condition' => null, 'district' => 'রাজশাহী', 'upazila' => 'পবা', 'mobile' => '01711000021', 'desc' => 'সেচের জন্য পাম্প ভাড়া, দৈনিক হিসেবে।', 'featured' => false],
        ];
    }
}
