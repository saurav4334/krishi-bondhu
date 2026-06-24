<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use Illuminate\Database\Seeder;

/**
 * Full taxonomy for the কৃষি সরঞ্জাম (equipment & agri-input) marketplace.
 * Seven main categories, each with subcategories (parent_id hierarchy).
 *
 * Idempotent: updateOrCreate by slug, so re-running never duplicates and keeps
 * names/parents in sync. Legacy main slugs are reconciled first, and retired
 * categories with no products are pruned so the taxonomy stays clean.
 *
 * Independent of the crop marketplace (marketplace_categories) — never mixed.
 */
class EquipmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Reconcile earlier slugs so existing rows (and any product links) are
        // updated in place rather than duplicated.
        $renames = ['eq-seeds' => 'seeds', 'eq-fertilizer' => 'fertilizer', 'pesticide' => 'pesticides'];
        foreach ($renames as $old => $new) {
            EquipmentCategory::whereNull('parent_id')->where('slug', $old)->update(['slug' => $new]);
        }

        $tree = $this->tree();
        $keepSlugs = [];
        $mainSort = 0;

        foreach ($tree as $main) {
            $parent = EquipmentCategory::updateOrCreate(
                ['slug' => $main['slug']],
                ['name' => $main['name'], 'icon' => $main['icon'], 'parent_id' => null, 'status' => 'active', 'sort_order' => $mainSort++]
            );
            $keepSlugs[] = $main['slug'];

            $childSort = 0;
            foreach ($main['children'] as [$name, $slug]) {
                EquipmentCategory::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'icon' => $main['icon'], 'parent_id' => $parent->id, 'status' => 'active', 'sort_order' => $childSort++]
                );
                $keepSlugs[] = $slug;
            }
        }

        // Prune retired categories that carry no products (safe taxonomy cleanup).
        EquipmentCategory::whereNotIn('slug', $keepSlugs)->get()->each(function (EquipmentCategory $cat) {
            if ($cat->products()->count() === 0) {
                $cat->delete();
            }
        });
    }

    private function tree(): array
    {
        return [
            ['name' => 'কৃষি যন্ত্রপাতি', 'slug' => 'machinery', 'icon' => '🚜', 'children' => [
                ['ট্রাক্টর', 'tractor'], ['পাওয়ার টিলার', 'power-tiller'], ['মিনি ট্রাক্টর', 'mini-tractor'],
                ['ওয়াকিং ট্রাক্টর', 'walking-tractor'], ['রোটাভেটর', 'rotavator'], ['লাঙ্গল', 'plough'],
                ['ডিস্ক হ্যারো', 'disc-harrow'], ['কাল্টিভেটর', 'cultivator'], ['লেভেলার', 'leveler'],
                ['সিড ড্রিল', 'seed-drill'], ['রাইস ট্রান্সপ্লান্টার', 'rice-transplanter'], ['সিডার মেশিন', 'seeder-machine'],
                ['প্ল্যান্টার', 'planter'], ['রিপার', 'reaper'], ['হারভেস্টার', 'harvester'],
                ['কম্বাইন হারভেস্টার', 'combine-harvester'], ['ঘাস কাটার মেশিন', 'grass-cutter'], ['ধান মাড়াই মেশিন', 'paddy-thresher'],
                ['গম মাড়াই মেশিন', 'wheat-thresher'], ['ভুট্টা শেলার', 'maize-sheller'], ['চাল প্রসেসিং মেশিন', 'rice-processing'],
                ['ডিজেল পাম্প', 'diesel-pump'], ['ইলেকট্রিক পাম্প', 'electric-pump'], ['সাবমারসিবল পাম্প', 'submersible-pump'],
                ['ড্রিপ ইরিগেশন', 'drip-irrigation'], ['স্প্রিংকলার', 'sprinkler'],
            ]],
            ['name' => 'বীজ', 'slug' => 'seeds', 'icon' => '🌱', 'children' => [
                ['BRRI ধান', 'brri-rice'], ['হাইব্রিড ধান', 'hybrid-rice'], ['সুগন্ধি ধান', 'aromatic-rice'],
                ['গম', 'wheat-seed'], ['হাইব্রিড ভুট্টা', 'hybrid-maize'], ['দেশি ভুট্টা', 'local-maize'],
                ['টমেটো', 'tomato-seed'], ['বেগুন', 'brinjal-seed'], ['মরিচ', 'chili-seed'],
                ['ফুলকপি', 'cauliflower-seed'], ['বাঁধাকপি', 'cabbage-seed'], ['শসা', 'cucumber-seed'],
                ['মসুর', 'lentil-seed'], ['মুগ', 'mung-seed'], ['ছোলা', 'chickpea-seed'],
                ['সরিষা', 'mustard-seed'], ['সূর্যমুখী', 'sunflower-seed'], ['তিল', 'sesame-seed'],
            ]],
            ['name' => 'সার', 'slug' => 'fertilizer', 'icon' => '🧪', 'children' => [
                ['ইউরিয়া', 'urea'], ['টিএসপি', 'tsp'], ['ডিএপি', 'dap'], ['এমওপি', 'mop'],
                ['ভার্মি কম্পোস্ট', 'vermicompost'], ['গোবর সার', 'cow-dung'], ['অর্গানিক কম্পোস্ট', 'organic-compost'],
                ['লিকুইড ফার্টিলাইজার', 'liquid-fertilizer'], ['মাইক্রোনিউট্রিয়েন্ট', 'micronutrient'],
                ['ফল গাছের সার', 'fruit-fertilizer'], ['সবজি সার', 'vegetable-fertilizer'], ['ফুলের সার', 'flower-fertilizer'],
            ]],
            ['name' => 'কৃষি ঔষধ', 'slug' => 'pesticides', 'icon' => '🧴', 'children' => [
                ['কীটনাশক', 'insecticide'], ['ছত্রাকনাশক', 'fungicide'], ['আগাছানাশক', 'herbicide'],
                ['জৈব বালাইনাশক', 'bio-pesticide'], ['ইঁদুর নিয়ন্ত্রণ', 'rodent-control'],
            ]],
            ['name' => 'কৃষি সরঞ্জাম', 'slug' => 'tools', 'icon' => '🧰', 'children' => [
                ['হ্যান্ড স্প্রেয়ার', 'hand-sprayer'], ['পাওয়ার স্প্রেয়ার', 'power-sprayer'], ['ব্যাটারি স্প্রেয়ার', 'battery-sprayer'],
                ['কোদাল', 'kodal'], ['কাস্তে', 'kaste'], ['বেলচা', 'shovel'], ['কুড়াল', 'axe'],
                ['হোস পাইপ', 'hose-pipe'], ['পিভিসি পাইপ', 'pvc-pipe'], ['কানেক্টর', 'connector'],
                ['গ্লাভস', 'gloves'], ['মাস্ক', 'mask'], ['গামবুট', 'gumboot'],
            ]],
            ['name' => 'প্রাণিসম্পদ ও পোল্ট্রি', 'slug' => 'livestock', 'icon' => '🐄', 'children' => [
                ['গরুর খাদ্য', 'cattle-feed'], ['গরুর ভিটামিন', 'cattle-vitamin'], ['ছাগলের খাদ্য', 'goat-feed'],
                ['মুরগির খাদ্য', 'poultry-feed'], ['ব্রয়লার', 'broiler'], ['লেয়ার', 'layer'],
                ['মাছের খাদ্য', 'fish-feed'], ['পুকুর ওষুধ', 'pond-medicine'],
            ]],
            ['name' => 'কৃষি সেবা', 'slug' => 'agri-services', 'icon' => '🛠️', 'children' => [
                ['ট্রাক্টর ভাড়া', 'tractor-rental'], ['হারভেস্টার ভাড়া', 'harvester-rental'], ['সেচ পাম্প ভাড়া', 'pump-rental'],
                ['স্প্রে সার্ভিস', 'spray-service'], ['কৃষি শ্রমিক', 'farm-labor'], ['পরিবহন সেবা', 'transport-service'],
            ]],
        ];
    }
}
