<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds the company's real product catalog, sourced directly from Gazi's own
 * public sites: the industrial pumps/motors showcase (gazipumps.com) and the
 * Gazi Smiss home-appliance storefront (gcart.com.bd). Names, prices, and
 * product photos are real — pulled and downloaded from those sites, not
 * generated — so this seeds explicit rows rather than using factories (see
 * UserSeeder for the same reasoning applied to real user accounts).
 */
class ProductSeeder extends Seeder
{
    private const CATEGORIES = [
        [
            'name' => 'Industrial Pumps',
            'code' => 'CAT-001',
            'description' => 'Self-priming, centrifugal, and submersible pumps from the Gazi, Pentax, Eifel, and CNP lines (gazipumps.com).',
            'products' => [
                ['name' => 'Gazi Self-Priming Jet Pump', 'sku' => 'SKU-00001', 'price' => 6500, 'description' => 'Gazi self-priming jet pump for household and light commercial water supply.', 'image' => 'gazi-self-priming-jet-pump.jpg'],
                ['name' => 'Gazi Standardized Centrifugal Pump', 'sku' => 'SKU-00002', 'price' => 8200, 'description' => 'Gazi standardized centrifugal pump for general-purpose water transfer.', 'image' => 'gazi-standardized-centrifugal-pump.jpg'],
                ['name' => 'Pentax Centrifugal Pump', 'sku' => 'SKU-00003', 'price' => 7800, 'description' => 'Pentax (Pentex) centrifugal pump, distributed by Gazi Pumps & Motors.', 'image' => 'pentax-centrifugal-pump.jpg'],
                ['name' => 'Pentax Submersible Pump', 'sku' => 'SKU-00004', 'price' => 9500, 'description' => 'Pentax (Pentex) submersible pump for deep-well water extraction.', 'image' => 'pentax-submersible-pump.jpg'],
                ['name' => 'Eifel EA Series Pump', 'sku' => 'SKU-00005', 'price' => 8800, 'description' => 'Eifel EA Series pump, distributed by Gazi Pumps & Motors.', 'image' => 'eifel-ea-series-pump.jpg'],
                ['name' => 'Eifel EAD Series Pump', 'sku' => 'SKU-00006', 'price' => 9200, 'description' => 'Eifel EAD Series pump, distributed by Gazi Pumps & Motors.', 'image' => 'eifel-ead-series-pump.jpg'],
                ['name' => 'CNP CDLF Series Pump', 'sku' => 'SKU-00007', 'price' => 15500, 'description' => 'CNP CDLF Series vertical multistage pump, distributed by Gazi Pumps & Motors.', 'image' => 'cnp-cdlf-series-pump.jpg'],
                ['name' => 'CNP SZ Series Pump', 'sku' => 'SKU-00008', 'price' => 14200, 'description' => 'CNP SZ Series pump, distributed by Gazi Pumps & Motors.', 'image' => 'cnp-sz-series-pump.jpg'],
            ],
        ],
        [
            'name' => 'Industrial Motors & Equipment',
            'code' => 'CAT-002',
            'description' => 'Fire-fighting pump sets, motors, gas stoves, and tubewells from the Gazi Pumps & Motors industrial line (gazipumps.com).',
            'products' => [
                ['name' => 'Gazi Fire Fighting Pump Complete Set', 'sku' => 'SKU-00009', 'price' => 185000, 'description' => 'Gazi fire fighting pump complete set for industrial fire-safety installations.', 'image' => 'gazi-fire-fighting-pump-set.jpg'],
                ['name' => 'Gazi Motors YC Series', 'sku' => 'SKU-00010', 'price' => 8400, 'description' => 'Gazi Motors YC Series electric motor for industrial and pump applications.', 'image' => 'gazi-motors-yc-series.jpg'],
                ['name' => 'Gazi Gas Stove (Industrial Line)', 'sku' => 'SKU-00011', 'price' => 3200, 'description' => 'Gazi gas stove from the Gazi Pumps & Motors industrial product line.', 'image' => 'gazi-gas-stove-industrial.jpg'],
                ['name' => 'Gazi Tubewell', 'sku' => 'SKU-00012', 'price' => 4500, 'description' => 'Gazi tubewell equipment for groundwater extraction.', 'image' => 'gazi-tubewell.jpg'],
            ],
        ],
        [
            'name' => 'Gazi Smiss Gas Stoves',
            'code' => 'CAT-003',
            'description' => 'Gazi Smiss and Gazi branded gas stoves from the gcart.com.bd storefront.',
            'products' => [
                ['name' => 'TG-206 - Gazi Smiss Gas Stove', 'sku' => 'SKU-00013', 'price' => 8466, 'description' => 'Gazi Smiss TG-206 double burner gas stove.', 'image' => 'gazi-smiss-gas-stove-tg-206.png'],
                ['name' => 'GST-102C - Gazi Gas Stove', 'sku' => 'SKU-00014', 'price' => 1864, 'description' => 'Gazi GST-102C single burner gas stove.', 'image' => 'gazi-gas-stove-gst-102c.png'],
                ['name' => 'EG-732S - Gazi Smiss Gas Stove', 'sku' => 'SKU-00015', 'price' => 14448, 'description' => 'Gazi Smiss EG-732S glass-top gas stove.', 'image' => 'gazi-smiss-gas-stove-eg-732s.webp'],
                ['name' => 'TG-213S - Gazi Smiss Gas Stove', 'sku' => 'SKU-00016', 'price' => 11220, 'description' => 'Gazi Smiss TG-213S double burner gas stove.', 'image' => 'gazi-smiss-gas-stove-tg-213s.png'],
                ['name' => 'GH-8204M - Gazi Smiss Gas Stove', 'sku' => 'SKU-00017', 'price' => 16128, 'description' => 'Gazi Smiss GH-8204M gas stove.', 'image' => 'gazi-smiss-gas-stove-gh-8204m.webp'],
            ],
        ],
        [
            'name' => 'Gazi Smiss Kitchen Hoods',
            'code' => 'CAT-004',
            'description' => 'Gazi Smiss kitchen hood/chimney models from the gcart.com.bd storefront.',
            'products' => [
                ['name' => 'HY-716BV - Gazi Smiss Kitchen Hood', 'sku' => 'SKU-00018', 'price' => 22176, 'description' => 'Gazi Smiss HY-716BV kitchen hood.', 'image' => 'gazi-smiss-kitchen-hood-hy-716bv.png'],
                ['name' => 'HY-712BT - Gazi Smiss Kitchen Hood', 'sku' => 'SKU-00019', 'price' => 14616, 'description' => 'Gazi Smiss HY-712BT kitchen hood.', 'image' => 'gazi-smiss-kitchen-hood-hy-712bt.webp'],
                ['name' => 'EG-750S - Gazi Smiss Kitchen Hood', 'sku' => 'SKU-00020', 'price' => 8856, 'description' => 'Gazi Smiss EG-750S kitchen hood.', 'image' => 'gazi-smiss-kitchen-hood-eg-750s.png'],
                ['name' => 'HY-736BV - Gazi Smiss Kitchen Hood', 'sku' => 'SKU-00021', 'price' => 23184, 'description' => 'Gazi Smiss HY-736BV kitchen hood.', 'image' => 'gazi-smiss-kitchen-hood-hy-736bv.webp'],
                ['name' => 'HY-729CP - Gazi Smiss Kitchen Hood', 'sku' => 'SKU-00022', 'price' => 16320, 'description' => 'Gazi Smiss HY-729CP kitchen hood.', 'image' => 'gazi-smiss-kitchen-hood-hy-729cp.webp'],
            ],
        ],
        [
            'name' => 'Gazi Smiss Induction & Infrared Cookers',
            'code' => 'CAT-005',
            'description' => 'Gazi Smiss induction and infrared cooktops from the gcart.com.bd storefront.',
            'products' => [
                ['name' => 'IF-HL01 - Gazi Smiss Infrared Cooker', 'sku' => 'SKU-00023', 'price' => 5952, 'description' => 'Gazi Smiss IF-HL01 infrared cooker.', 'image' => 'gazi-smiss-infrared-cooker-if-hl01.webp'],
                ['name' => 'A-40G - Gazi Smiss Infrared Cooker', 'sku' => 'SKU-00024', 'price' => 4080, 'description' => 'Gazi Smiss A-40G infrared cooker.', 'image' => 'gazi-smiss-infrared-cooker-a-40g.webp'],
                ['name' => 'A-25S - Gazi Smiss Induction Cooker', 'sku' => 'SKU-00025', 'price' => 4080, 'description' => 'Gazi Smiss A-25S induction cooker.', 'image' => 'gazi-smiss-induction-cooker-a-25s.webp'],
                ['name' => 'A-37G - Gazi Smiss Infrared Cooker', 'sku' => 'SKU-00026', 'price' => 4080, 'description' => 'Gazi Smiss A-37G infrared cooker.', 'image' => 'gazi-smiss-infrared-cooker-a-37g.png'],
                ['name' => 'E-720B - Gazi Smiss Induction & Infrared Cooker', 'sku' => 'SKU-00027', 'price' => 12960, 'description' => 'Gazi Smiss E-720B combined induction and infrared cooker.', 'image' => 'gazi-smiss-induction-infrared-cooker-e-720b.png'],
                ['name' => 'A-01 - Gazi Smiss Infrared Cooker', 'sku' => 'SKU-00028', 'price' => 3672, 'description' => 'Gazi Smiss A-01 infrared cooker.', 'image' => 'gazi-smiss-infrared-cooker-a-01.png'],
            ],
        ],
        [
            'name' => 'Gazi Smiss Electric Ovens',
            'code' => 'CAT-006',
            'description' => 'Gazi Smiss electric oven models from the gcart.com.bd storefront.',
            'products' => [
                ['name' => 'GEO-03 - Gazi Smiss Electric Oven 30 Liter', 'sku' => 'SKU-00029', 'price' => 15600, 'description' => 'Gazi Smiss GEO-03 electric oven, 30 liter capacity.', 'image' => 'gazi-smiss-electric-oven-30l-geo-03.png'],
                ['name' => 'GEO-04 - Gazi Smiss Electric Oven 40 Liter', 'sku' => 'SKU-00030', 'price' => 16800, 'description' => 'Gazi Smiss GEO-04 electric oven, 40 liter capacity.', 'image' => 'gazi-smiss-electric-oven-40l-geo-04.png'],
                ['name' => 'GEO-05 - Gazi Smiss Electric Oven 50 Liter', 'sku' => 'SKU-00031', 'price' => 19200, 'description' => 'Gazi Smiss GEO-05 electric oven, 50 liter capacity.', 'image' => 'gazi-smiss-electric-oven-50l-geo-05.png'],
            ],
        ],
        [
            'name' => 'Gazi Smiss Air Fryers',
            'code' => 'CAT-007',
            'description' => 'Gazi Smiss air fryer models from the gcart.com.bd storefront.',
            'products' => [
                ['name' => 'GA-AF-23 - Gazi Smiss Air Fryer', 'sku' => 'SKU-00032', 'price' => 11088, 'description' => 'Gazi Smiss GA-AF-23 air fryer.', 'image' => 'gazi-smiss-air-fryer-ga-af-23.png'],
                ['name' => 'GA-AF-25 - Gazi Smiss Air Fryer', 'sku' => 'SKU-00033', 'price' => 10836, 'description' => 'Gazi Smiss GA-AF-25 air fryer.', 'image' => 'gazi-smiss-air-fryer-ga-af-25.png'],
                ['name' => 'GA-AF-27 - Gazi Smiss Air Fryer', 'sku' => 'SKU-00034', 'price' => 8976, 'description' => 'Gazi Smiss GA-AF-27 air fryer.', 'image' => 'gazi-smiss-air-fryer-ga-af-27.png'],
            ],
        ],
        [
            'name' => 'Gazi Smiss Motors',
            'code' => 'CAT-008',
            'description' => 'Gazi Smiss Y2/YC series electric motors from the gcart.com.bd storefront.',
            'products' => [
                ['name' => '15.0 HP Y2 Motor 950 RPM', 'sku' => 'SKU-00035', 'price' => 87675, 'description' => 'Gazi Smiss Y2 series electric motor, 15.0 HP, 950 RPM.', 'image' => 'gazi-smiss-motor-15hp-y2.png'],
                ['name' => '10.0 HP Y2 Motor 2800 RPM', 'sku' => 'SKU-00036', 'price' => 34650, 'description' => 'Gazi Smiss Y2 series electric motor, 10.0 HP, 2800 RPM.', 'image' => 'gazi-smiss-motor-10hp-y2.png'],
                ['name' => '5.5 HP Y2 Motor 2800 RPM', 'sku' => 'SKU-00037', 'price' => 24700, 'description' => 'Gazi Smiss Y2 series electric motor, 5.5 HP, 2800 RPM.', 'image' => 'gazi-smiss-motor-5-5hp-y2.png'],
                ['name' => '3 HP YC Motor 1450 RPM', 'sku' => 'SKU-00038', 'price' => 21000, 'description' => 'Gazi Smiss YC series electric motor, 3 HP, 1450 RPM.', 'image' => 'gazi-smiss-motor-3hp-yc.png'],
                ['name' => '1.0 HP Y2 Motor 2800 RPM', 'sku' => 'SKU-00039', 'price' => 9450, 'description' => 'Gazi Smiss Y2 series electric motor, 1.0 HP, 2800 RPM.', 'image' => 'gazi-smiss-motor-1hp-y2.png'],
                ['name' => '0.75 HP YC Motor 1450 RPM (Classic)', 'sku' => 'SKU-00040', 'price' => 8400, 'description' => 'Gazi Smiss YC series electric motor (Classic), 0.75 HP, 1450 RPM.', 'image' => 'gazi-smiss-motor-0-75hp-yc-classic.png'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $categoryData) {
            $category = ProductCategory::create([
                'name' => $categoryData['name'],
                'code' => $categoryData['code'],
                'description' => $categoryData['description'],
                'status' => true,
            ]);

            foreach ($categoryData['products'] as $productData) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'sku' => $productData['sku'],
                    'price' => $productData['price'],
                    'description' => $productData['description'],
                    'image' => 'products/'.$productData['image'],
                    'status' => true,
                ]);
            }
        }
    }
}
