<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\District;
use App\Models\Division;
use App\Models\Thana;
use Illuminate\Database\Seeder;

/**
 * Loads the real Bangladesh administrative hierarchy (8 divisions, 64
 * districts, 494 upazilas/thanas) from database/data/bd-*.json — sourced
 * from the nuhil/bangladesh-geocode open dataset (MIT licensed, itself
 * compiled from bangladesh.gov.bd / Wikipedia / Google Maps). IDs are
 * preserved exactly as given in the source files so that
 * database/data/territory-thana-map.json (built against those same ids by
 * TerritoryGeoBackfillSeeder) stays valid.
 *
 * Idempotent: re-running skips any row whose id already exists, so this is
 * safe to run again on staging/production without duplicating rows.
 */
class DivisionDistrictThanaSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDivisions();
        $this->seedDistricts();
        $this->seedThanas();
    }

    private function seedDivisions(): void
    {
        $rows = json_decode(file_get_contents(database_path('data/bd-divisions.json')), true);

        foreach ($rows as $row) {
            if (Division::withTrashed()->find($row['id'])) {
                continue;
            }

            $division = new Division([
                'name' => $row['name'],
                'name_bn' => $row['name_bn'],
                'status' => true,
            ]);
            $division->id = $row['id'];
            $division->save();
        }
    }

    private function seedDistricts(): void
    {
        $rows = json_decode(file_get_contents(database_path('data/bd-districts.json')), true);

        foreach ($rows as $row) {
            if (District::withTrashed()->find($row['id'])) {
                continue;
            }

            $district = new District([
                'division_id' => $row['division_id'],
                'name' => $row['name'],
                'name_bn' => $row['name_bn'],
                'status' => true,
            ]);
            $district->id = $row['id'];
            $district->save();
        }
    }

    private function seedThanas(): void
    {
        $rows = json_decode(file_get_contents(database_path('data/bd-upazilas.json')), true);

        foreach ($rows as $row) {
            if (Thana::withTrashed()->find($row['id'])) {
                continue;
            }

            $thana = new Thana([
                'district_id' => $row['district_id'],
                'name' => $row['name'],
                'name_bn' => $row['name_bn'],
                'status' => true,
            ]);
            $thana->id = $row['id'];
            $thana->save();
        }
    }
}
