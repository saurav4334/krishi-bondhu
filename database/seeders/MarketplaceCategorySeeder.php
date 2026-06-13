<?php

namespace Database\Seeders;

use App\Models\MarketplaceCategory;
use Illuminate\Database\Seeder;

class MarketplaceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'বীজ', 'slug' => 'seeds', 'icon' => '🌱'],
            ['name' => 'সার', 'slug' => 'fertilizer', 'icon' => '🧪'],
            ['name' => 'কৃষি যন্ত্রপাতি', 'slug' => 'equipment', 'icon' => '🚜'],
            ['name' => 'গবাদিপশু', 'slug' => 'livestock', 'icon' => '🐄'],
            ['name' => 'ফসল ও শস্য', 'slug' => 'crops', 'icon' => '🌾'],
        ];

        foreach ($categories as $cat) {
            MarketplaceCategory::firstOrCreate(['slug' => $cat['slug']], $cat + ['status' => 'active']);
        }
    }
}
