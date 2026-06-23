<?php

namespace Database\Seeders;

use App\Models\MarketplaceCategory;
use Illuminate\Database\Seeder;

/**
 * The ফসল বিক্রয় (crop trading) marketplace is dedicated to crops ONLY.
 * Equipment, seeds, fertilizer and tools live in the separate
 * কৃষি সরঞ্জাম marketplace (see EquipmentCategorySeeder).
 */
class MarketplaceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'ধান', 'slug' => 'dhan', 'icon' => '🌾'],
            ['name' => 'গম', 'slug' => 'gom', 'icon' => '🌾'],
            ['name' => 'ভুট্টা', 'slug' => 'bhutta', 'icon' => '🌽'],
            ['name' => 'পাট', 'slug' => 'pat', 'icon' => '🪴'],
            ['name' => 'আলু', 'slug' => 'alu', 'icon' => '🥔'],
            ['name' => 'সবজি', 'slug' => 'sobji', 'icon' => '🥬'],
            ['name' => 'ফল', 'slug' => 'fol', 'icon' => '🍎'],
            ['name' => 'ডাল', 'slug' => 'dal', 'icon' => '🫘'],
            ['name' => 'তেলবীজ', 'slug' => 'telbij', 'icon' => '🌻'],
        ];

        foreach ($categories as $cat) {
            MarketplaceCategory::firstOrCreate(['slug' => $cat['slug']], $cat + ['status' => 'active']);
        }
    }
}
