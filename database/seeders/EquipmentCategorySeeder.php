<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use Illuminate\Database\Seeder;

class EquipmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            ['name' => 'কৃষি যন্ত্রপাতি', 'slug' => 'machinery', 'icon' => '🚜', 'children' => [
                ['ট্রাক্টর', 'tractor'],
                ['পাওয়ার টিলার', 'power-tiller'],
                ['হারভেস্টার', 'harvester'],
                ['ধান মাড়াই মেশিন', 'thresher'],
                ['সেচ পাম্প', 'irrigation-pump'],
                ['স্প্রে মেশিন', 'sprayer-machine'],
            ]],
            ['name' => 'কৃষি সরঞ্জাম', 'slug' => 'tools', 'icon' => '🧰', 'children' => [
                ['কোদাল', 'kodal'],
                ['কাস্তে', 'kaste'],
                ['পাইপ', 'pipe'],
                ['কৃষি টুলস', 'farm-tools'],
            ]],
            ['name' => 'বীজ', 'slug' => 'eq-seeds', 'icon' => '🌱', 'children' => [
                ['ধান বীজ', 'rice-seed'],
                ['ভুট্টা বীজ', 'maize-seed'],
                ['সবজি বীজ', 'vegetable-seed'],
            ]],
            ['name' => 'সার', 'slug' => 'eq-fertilizer', 'icon' => '🧪', 'children' => [
                ['ইউরিয়া', 'urea'],
                ['টিএসপি', 'tsp'],
                ['ডিএপি', 'dap'],
                ['জৈব সার', 'organic-fertilizer'],
            ]],
            ['name' => 'কৃষি ঔষধ', 'slug' => 'pesticide', 'icon' => '🧴', 'children' => [
                ['কীটনাশক', 'insecticide'],
                ['ফাঙ্গিসাইড', 'fungicide'],
                ['হার্বিসাইড', 'herbicide'],
            ]],
        ];

        $mainSort = 0;
        foreach ($tree as $main) {
            $parent = EquipmentCategory::updateOrCreate(
                ['slug' => $main['slug']],
                ['name' => $main['name'], 'icon' => $main['icon'], 'parent_id' => null, 'status' => 'active', 'sort_order' => $mainSort++]
            );

            $childSort = 0;
            foreach ($main['children'] as [$name, $slug]) {
                EquipmentCategory::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'icon' => $main['icon'], 'parent_id' => $parent->id, 'status' => 'active', 'sort_order' => $childSort++]
                );
            }
        }
    }
}
