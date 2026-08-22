<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. Each module appends its own seeder
     * call here, in dependency order.
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            DivisionDistrictThanaSeeder::class,
            OrgStructureSeeder::class,
            TerritoryGeoBackfillSeeder::class,
            DealerSeeder::class,
            ProductSeeder::class,
            AttendanceSeeder::class,
            GpsLogSeeder::class,
            VisitSeeder::class,
            OrderSeeder::class,
            CollectionEntrySeeder::class,
            TargetSeeder::class,
            NotificationSeeder::class,
            NewsSeeder::class,
            PromotionSeeder::class,
            FaqSeeder::class,
            ServiceCenterSeeder::class,
            BrochureSeeder::class,
            CustomerAccountSeeder::class,
            InquirySeeder::class,
            VisitRequestSeeder::class,
        ]);
    }
}
