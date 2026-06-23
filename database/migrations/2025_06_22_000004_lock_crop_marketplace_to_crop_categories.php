<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Dedicate the ফসল বিক্রয় (crop trading) marketplace to crops ONLY.
 *
 * Ensures the nine crop categories exist and are active, and deactivates the
 * legacy non-crop categories (seeds, fertilizer, equipment, livestock and the
 * generic "crops") so they no longer appear in the crop module. Those product
 * types now live in the separate কৃষি সরঞ্জাম (equipment) marketplace.
 *
 * Deactivation (not deletion) keeps any existing listings intact.
 */
return new class extends Migration
{
    private array $cropCategories = [
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

    private array $legacyNonCrop = ['seeds', 'fertilizer', 'equipment', 'livestock', 'crops'];

    public function up(): void
    {
        foreach ($this->cropCategories as $cat) {
            $exists = DB::table('marketplace_categories')->where('slug', $cat['slug'])->exists();
            if ($exists) {
                DB::table('marketplace_categories')->where('slug', $cat['slug'])
                    ->update(['name' => $cat['name'], 'icon' => $cat['icon'], 'status' => 'active', 'updated_at' => now()]);
            } else {
                DB::table('marketplace_categories')->insert($cat + [
                    'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        DB::table('marketplace_categories')->whereIn('slug', $this->legacyNonCrop)
            ->update(['status' => 'inactive', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('marketplace_categories')->whereIn('slug', $this->legacyNonCrop)
            ->update(['status' => 'active', 'updated_at' => now()]);
    }
};
