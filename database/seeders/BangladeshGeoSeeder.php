<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds all 8 divisions, 64 districts and ~494 upazilas of Bangladesh
 * from the canonical dataset in database/data/*.json.
 */
class BangladeshGeoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $divisions = array_map(fn ($d) => [
            'id' => $d['id'],
            'name' => $d['name'],
            'bn_name' => $d['bn_name'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->load('divisions.json'));

        $districts = array_map(fn ($d) => [
            'id' => $d['id'],
            'division_id' => $d['division_id'],
            'name' => $d['name'],
            'bn_name' => $d['bn_name'],
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->load('districts.json'));

        $upazilas = array_map(fn ($u) => [
            'id' => $u['id'],
            'district_id' => $u['district_id'],
            'name' => $u['name'],
            'bn_name' => $u['bn_name'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->load('upazilas.json'));

        DB::table('divisions')->insert($divisions);
        foreach (array_chunk($districts, 100) as $chunk) {
            DB::table('districts')->insert($chunk);
        }
        foreach (array_chunk($upazilas, 200) as $chunk) {
            DB::table('upazilas')->insert($chunk);
        }

        $this->command->info("  Geo: {$this->c($divisions)} divisions, {$this->c($districts)} districts, {$this->c($upazilas)} upazilas seeded.");
    }

    /** Read the "table" data block out of a phpMyAdmin-style JSON export. */
    private function load(string $file): array
    {
        $blocks = json_decode(file_get_contents(database_path("data/{$file}")), true);
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'table') {
                return $block['data'];
            }
        }
        return [];
    }

    private function c(array $a): int
    {
        return count($a);
    }
}
