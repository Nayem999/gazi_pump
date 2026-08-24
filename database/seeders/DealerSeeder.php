<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CustomerType;
use App\Models\Dealer;
use App\Models\District;
use App\Models\Division;
use App\Models\Territory;
use App\Models\Thana;
use Illuminate\Database\Seeder;

/**
 * Sample data requested after the demo-data purge: 3 territories in Dhaka
 * (division/district already exist from the geo import, which the purge
 * kept) and 5 dealers spread across them. No dealer-network photography
 * exists anywhere on the client's own sites (gazipumps.com, gcart.com.bd,
 * gazihomeappliance.com are product catalogs, not dealer directories), so
 * each dealer reuses one of three real Gazi photos (company
 * showroom/event images) rather than a fabricated placeholder — these are
 * genuine company photos, just not unique per dealer.
 */
class DealerSeeder extends Seeder
{
    private const IMAGES = [
        'dealers/gazi-showroom-1.jpg',
        'dealers/gazi-showroom-2.jpg',
        'dealers/gazi-showroom-3.jpg',
    ];

    private const TERRITORIES = [
        ['name' => 'Dhaka - Savar', 'code' => 'TER-DHK-001', 'thana' => 'Savar'],
        ['name' => 'Dhaka - Keraniganj', 'code' => 'TER-DHK-002', 'thana' => 'Keraniganj'],
        ['name' => 'Dhaka - Dhamrai', 'code' => 'TER-DHK-003', 'thana' => 'Dhamrai'],
    ];

    private const DEALERS = [
        ['name' => 'Savar Pump House', 'type' => CustomerType::Dealer, 'address' => 'Savar Bazar Road, Savar, Dhaka'],
        ['name' => 'Keraniganj Hardware & Motors', 'type' => CustomerType::Retailer, 'address' => 'Aganagar, Keraniganj, Dhaka'],
        ['name' => 'Dhamrai Water Solutions', 'type' => CustomerType::Dealer, 'address' => 'Dhamrai Bus Stand, Dhamrai, Dhaka'],
        ['name' => 'Gazi Appliance Corner', 'type' => CustomerType::Retailer, 'address' => 'Savar New Market, Savar, Dhaka'],
        ['name' => 'Buriganga Distribution House', 'type' => CustomerType::Distributor, 'address' => 'Zinzira, Keraniganj, Dhaka'],
    ];

    public function run(): void
    {
        $division = Division::where('name', 'Dhaka')->first();
        $district = District::where('division_id', $division?->id)->where('name', 'Dhaka')->first();

        if (! $division || ! $district) {
            return;
        }

        $territories = collect(self::TERRITORIES)->map(function (array $data) use ($division, $district) {
            $thana = Thana::where('district_id', $district->id)->where('name', $data['thana'])->first();

            return Territory::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'division_id' => $division->id,
                'district_id' => $district->id,
                'thana_id' => $thana?->id,
                'status' => true,
            ]);
        });

        collect(self::DEALERS)->each(function (array $data, int $i) use ($division, $district, $territories) {
            $territory = $territories->get($i % $territories->count());

            Dealer::create([
                'dealer_code' => 'DLR-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'name' => $data['name'],
                'type' => $data['type'],
                'phone' => '01'.str_pad((string) random_int(700000000, 999999999), 9, '0', STR_PAD_LEFT),
                'address' => $data['address'],
                'image' => self::IMAGES[$i % count(self::IMAGES)],
                'division_id' => $division->id,
                'district_id' => $district->id,
                'thana_id' => $territory->thana_id,
                'territory_id' => $territory->id,
                'status' => true,
            ]);
        });
    }
}
