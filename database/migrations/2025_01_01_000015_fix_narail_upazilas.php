<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data repair: ensure নড়াইল (Narail) district has its 3 upazilas.
 *
 * On some installs the seeded Narail (id 23) was deleted & re-added via the admin
 * "Add district" panel, producing a Narail district with no upazilas — which left the
 * registration Upazila dropdown empty for Narail.
 *
 * Idempotent and safe on a fresh DB: if no Narail-like district is missing its
 * upazilas, it does nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        $upazilas = [
            'নড়াইল সদর' => 'Narail Sadar',
            'লোহাগড়া'   => 'Lohagara',
            'কালিয়া'     => 'Kalia',
        ];

        $hasNoUpazilas = function ($query) {
            $query->select(DB::raw(1))
                ->from('upazilas')
                ->whereColumn('upazilas.district_id', 'districts.id');
        };

        // Primary: the Khulna-division (id 3) district that has zero upazilas == the orphaned Narail.
        $district = DB::table('districts')
            ->where('division_id', 3)
            ->whereNotExists($hasNoUpazilas)
            ->first();

        // Fallback: any district named নড়াইল that has zero upazilas.
        if (! $district) {
            $district = DB::table('districts')
                ->where('bn_name', 'নড়াইল')
                ->whereNotExists($hasNoUpazilas)
                ->first();
        }

        // Nothing to repair (e.g. a clean seed where Narail already has its upazilas).
        if (! $district) {
            return;
        }

        $now = now();
        foreach ($upazilas as $bn => $en) {
            $exists = DB::table('upazilas')
                ->where('district_id', $district->id)
                ->where('bn_name', $bn)
                ->exists();

            if (! $exists) {
                DB::table('upazilas')->insert([
                    'district_id' => $district->id,
                    'name' => $en,
                    'bn_name' => $bn,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // No-op: this is a forward-only data repair.
    }
};
