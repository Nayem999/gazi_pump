<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\TallyEntityType;
use App\Enums\TallyMappingSyncStatus;
use App\Models\TallyMapping;
use App\Services\TallyMappingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallyMappingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): TallyMappingService
    {
        return app(TallyMappingService::class);
    }

    public function test_find_or_create_mapping_creates_a_new_row_when_none_exists(): void
    {
        $mapping = $this->service()->findOrCreateMapping(TallyEntityType::Dealer, 5, 'GUID-1', 'Rahman Enterprise', '10');

        $this->assertDatabaseHas('tally_mappings', [
            'entity_type' => TallyEntityType::Dealer->value,
            'sfa_id' => 5,
            'tally_guid' => 'GUID-1',
        ]);
        $this->assertSame(TallyMappingSyncStatus::Synced, $mapping->sync_status);
    }

    public function test_find_or_create_mapping_updates_the_existing_row_in_place_rather_than_duplicating(): void
    {
        $service = $this->service();
        $service->findOrCreateMapping(TallyEntityType::Dealer, 5, 'GUID-1', 'Rahman Enterprise', '10');
        $service->findOrCreateMapping(TallyEntityType::Dealer, 5, 'GUID-1', 'M/S Rahman Enterprise', '11');

        $this->assertDatabaseCount('tally_mappings', 1);
        $this->assertDatabaseHas('tally_mappings', ['sfa_id' => 5, 'tally_name' => 'M/S Rahman Enterprise', 'tally_alter_id' => '11']);
    }

    public function test_a_tally_guid_already_owned_by_a_different_sfa_id_is_flagged_as_conflict_not_reassigned(): void
    {
        $service = $this->service();
        $service->findOrCreateMapping(TallyEntityType::Dealer, 1, 'GUID-SHARED', 'Dealer One');
        $service->findOrCreateMapping(TallyEntityType::Dealer, 2, null, 'Dealer Two');

        $second = $service->findOrCreateMapping(TallyEntityType::Dealer, 2, 'GUID-SHARED', 'Dealer Two');

        $this->assertSame(TallyMappingSyncStatus::Conflict, $second->sync_status);
        $this->assertDatabaseHas('tally_mappings', ['sfa_id' => 1, 'tally_guid' => 'GUID-SHARED', 'sync_status' => TallyMappingSyncStatus::Synced->value]);
    }

    public function test_resolve_by_sfa_id_and_by_tally_guid_are_scoped_to_the_entity_type(): void
    {
        TallyMapping::factory()->create(['entity_type' => TallyEntityType::Dealer, 'sfa_id' => 9, 'tally_guid' => 'G-9']);
        TallyMapping::factory()->create(['entity_type' => TallyEntityType::Retailer, 'sfa_id' => 9, 'tally_guid' => 'G-9-different']);

        $service = $this->service();

        $this->assertNull($service->resolveBySfaId(TallyEntityType::Product, 9));
        $this->assertNotNull($service->resolveBySfaId(TallyEntityType::Dealer, 9));
        $this->assertNull($service->resolveByTallyGuid(TallyEntityType::Retailer, 'G-9'));
    }
}
