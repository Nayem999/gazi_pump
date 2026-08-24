<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Territory;
use App\Models\Thana;
use Illuminate\Database\Seeder;

/**
 * Backfills division_id/district_id/thana_id on the pre-existing Territory
 * (Bangladesh Union/ADM4) records, which were imported from a GIS source
 * carrying no administrative-hierarchy information at all.
 *
 * database/data/territory-thana-map.json holds every territory→thana pair
 * where the territory's name matched exactly one upazila name nationwide
 * (built once, offline, against the same reference dataset
 * DivisionDistrictThanaSeeder loads — see that seeder's docblock). Only
 * ~38% of territories resolve unambiguously this way; the remainder are
 * left with null geo columns for an admin to assign by hand via the
 * Territory edit form (surfaced there by the "Missing Geo Data" filter) —
 * a deliberate, non-destructive partial backfill, not a bug.
 *
 * Idempotent and safe to re-run: only ever fills currently-null columns,
 * never overwrites an already-set or manually-corrected value.
 */
class TerritoryGeoBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $mapPath = database_path('data/territory-thana-map.json');

        if (! is_file($mapPath)) {
            $this->command?->warn('territory-thana-map.json not found — skipping backfill.');

            return;
        }

        $pairs = json_decode(file_get_contents($mapPath), true);
        $thanas = Thana::with('district')->get()->keyBy('id');

        $updated = 0;
        $skippedMissingThana = 0;

        foreach ($pairs as $pair) {
            $thana = $thanas->get($pair['thana_id']);

            if (! $thana) {
                $skippedMissingThana++;

                continue;
            }

            $territory = Territory::withTrashed()->find($pair['territory_id']);

            if (! $territory || $territory->thana_id !== null) {
                continue;
            }

            $territory->forceFill([
                'thana_id' => $thana->id,
                'district_id' => $thana->district_id,
                'division_id' => $thana->district->division_id,
            ])->save();

            $updated++;
        }

        $remaining = Territory::whereNull('thana_id')->count();

        $this->command?->info("Territory geo backfill: {$updated} territories mapped, {$remaining} remain unmapped (skipped-missing-thana: {$skippedMissingThana}).");
    }
}
