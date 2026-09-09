<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\TallyEntityType;
use App\Models\Dealer;
use App\Models\Depot;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TallyMapping;
use App\Services\TallyMasterSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Turning a pulled Tally master list into real mappings — the step whose
 * absence made "Sync Now" report success while leaving every dealer and
 * product unmapped.
 *
 * The rule under test throughout: a name may *bootstrap* a mapping, but
 * only a GUID ever resolves identity afterwards.
 */
class TallyMasterSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): TallyMasterSyncService
    {
        return app(TallyMasterSyncService::class);
    }

    public function test_an_unmapped_dealer_is_linked_by_name_on_the_first_pull(): void
    {
        $dealer = Dealer::factory()->create(['name' => 'Savar Pump House', 'tally_guid' => null]);

        $result = $this->service()->applyPulledMasters(TallyEntityType::Dealer, [
            ['name' => 'Savar Pump House', 'guid' => 'GUID-1'],
        ]);

        $this->assertSame(1, $result['linked']);
        $this->assertSame('GUID-1', $dealer->fresh()->tally_guid);
        $this->assertDatabaseHas('tally_mappings', [
            'entity_type' => 'dealer',
            'sfa_id' => $dealer->id,
            'tally_guid' => 'GUID-1',
        ]);
    }

    public function test_name_matching_ignores_case_and_surrounding_space(): void
    {
        $dealer = Dealer::factory()->create(['name' => 'Tangail Hardware', 'tally_guid' => null]);

        $result = $this->service()->applyPulledMasters(TallyEntityType::Dealer, [
            ['name' => '  tangail HARDWARE  ', 'guid' => 'GUID-2'],
        ]);

        $this->assertSame(1, $result['linked']);
        $this->assertSame('GUID-2', $dealer->fresh()->tally_guid);
    }

    public function test_a_second_pull_of_the_same_data_changes_nothing(): void
    {
        Dealer::factory()->create(['name' => 'Savar Pump House', 'tally_guid' => null]);
        $rows = [['name' => 'Savar Pump House', 'guid' => 'GUID-1']];

        $this->service()->applyPulledMasters(TallyEntityType::Dealer, $rows);
        $second = $this->service()->applyPulledMasters(TallyEntityType::Dealer, $rows);

        $this->assertSame(0, $second['linked']);
        $this->assertSame(1, $second['already']);
        $this->assertSame(1, TallyMapping::where('entity_type', 'dealer')->count());
    }

    public function test_a_dealer_already_holding_a_different_guid_is_reported_not_repointed(): void
    {
        // Same name, different Tally record. Silently re-pointing this would
        // move a whole ledger history onto the wrong account.
        $dealer = Dealer::factory()->create(['name' => 'Gazi Appliance Corner', 'tally_guid' => 'OLD-GUID']);

        $result = $this->service()->applyPulledMasters(TallyEntityType::Dealer, [
            ['name' => 'Gazi Appliance Corner', 'guid' => 'NEW-GUID'],
        ]);

        $this->assertSame(['Gazi Appliance Corner'], $result['conflicts']);
        $this->assertSame(0, $result['linked']);
        $this->assertSame('OLD-GUID', $dealer->fresh()->tally_guid, 'an existing mapping must never be moved silently');
    }

    public function test_a_renamed_tally_record_still_matches_on_guid(): void
    {
        $dealer = Dealer::factory()->create(['name' => 'Old SFA Name', 'tally_guid' => 'GUID-9']);

        $result = $this->service()->applyPulledMasters(TallyEntityType::Dealer, [
            ['name' => 'Renamed In Tally', 'guid' => 'GUID-9'],
        ]);

        $this->assertSame(1, $result['already']);
        $this->assertEmpty($result['imported'], 'a rename in Tally must not create a duplicate dealer here');
        $this->assertSame('Old SFA Name', $dealer->fresh()->name);
    }

    public function test_a_dealer_that_exists_only_in_tally_is_imported_inactive(): void
    {
        $result = $this->service()->applyPulledMasters(TallyEntityType::Dealer, [
            ['name' => 'Dealer 1', 'guid' => 'GUID-T1'],
        ]);

        $this->assertSame(['Dealer 1'], $result['imported']);

        $imported = Dealer::where('tally_guid', 'GUID-T1')->firstOrFail();

        // Tally supplies a name and a GUID; SFA requires a phone. The stub
        // must be unmistakably a stub — inactive, so it reaches nobody's
        // device and no report until a human fills it in.
        $this->assertFalse((bool) $imported->status);
        $this->assertSame('Dealer 1', $imported->name);
        $this->assertStringStartsWith('TLY-D-', $imported->dealer_code);
    }

    public function test_an_imported_product_is_inactive_and_priced_at_zero(): void
    {
        ProductCategory::factory()->create();

        $result = $this->service()->applyPulledMasters(TallyEntityType::Product, [
            ['name' => 'Pump Model 1', 'guid' => 'GUID-P1'],
        ]);

        $this->assertSame(['Pump Model 1'], $result['imported']);

        $product = Product::where('tally_guid', 'GUID-P1')->firstOrFail();

        $this->assertFalse((bool) $product->status);
        $this->assertSame(0.0, (float) $product->price, 'an invented price must never reach the field team');
    }

    public function test_a_product_cannot_be_imported_with_no_category_on_file(): void
    {
        // products.category_id is NOT NULL and Tally has no equivalent, so
        // there is no honest stub to create.
        $this->assertSame(0, ProductCategory::count());

        $result = $this->service()->applyPulledMasters(TallyEntityType::Product, [
            ['name' => 'Pump Model 1', 'guid' => 'GUID-P1'],
        ]);

        $this->assertSame(['Pump Model 1'], $result['unmatched']);
        $this->assertSame(0, Product::where('tally_guid', 'GUID-P1')->count());
    }

    public function test_a_godown_that_exists_only_in_tally_becomes_an_inactive_depot(): void
    {
        $result = $this->service()->applyPulledMasters(TallyEntityType::Depot, [
            ['name' => 'Warehouse', 'guid' => 'GUID-G1'],
        ]);

        $this->assertSame(['Warehouse'], $result['imported']);

        $depot = Depot::where('tally_guid', 'GUID-G1')->firstOrFail();

        $this->assertFalse((bool) $depot->status);
        $this->assertStringStartsWith('TLY-DEP-', $depot->code);
    }

    public function test_rows_missing_a_name_or_guid_are_ignored(): void
    {
        $result = $this->service()->applyPulledMasters(TallyEntityType::Dealer, [
            ['name' => '', 'guid' => 'GUID-X'],
            ['name' => 'No GUID Here', 'guid' => ''],
            ['name' => 'No GUID Here'],
        ]);

        $this->assertSame(0, $result['linked']);
        $this->assertEmpty($result['imported']);
        $this->assertSame(0, Dealer::count());
    }

    public function test_a_soft_deleted_mapping_row_does_not_break_the_next_sync(): void
    {
        // tally_mappings is unique on (entity_type, tally_guid) AND
        // (entity_type, sfa_id), and soft-deletes — so a trashed row still
        // holds both keys while being invisible to a default-scoped query.
        // This combination produced a live 1062 duplicate-key 500.
        $dealer = Dealer::factory()->create(['name' => 'Savar Pump House', 'tally_guid' => null]);
        $rows = [['name' => 'Savar Pump House', 'guid' => 'GUID-1']];

        $this->service()->applyPulledMasters(TallyEntityType::Dealer, $rows);

        TallyMapping::query()->delete();
        // Cleared through the query builder: $dealer is a stale instance
        // whose in-memory tally_guid is still null, so ->update() would see
        // nothing dirty and issue no query at all.
        Dealer::whereKey($dealer->id)->update(['tally_guid' => null]);

        $result = $this->service()->applyPulledMasters(TallyEntityType::Dealer, $rows);

        $this->assertSame(1, $result['linked']);
        $this->assertSame(1, TallyMapping::where('entity_type', 'dealer')->count());
        $this->assertSame('GUID-1', $dealer->fresh()->tally_guid);
    }

    public function test_a_guid_left_on_another_records_stale_mapping_is_cleared_not_crashed(): void
    {
        // The GUID sits on one mapping row and the SFA id on another; both
        // unique keys are occupied by rows this pull has to replace.
        $keeper = Dealer::factory()->create(['name' => 'Keeper', 'tally_guid' => null]);
        $gone = Dealer::factory()->create(['name' => 'Gone', 'tally_guid' => null]);

        TallyMapping::create([
            'entity_type' => TallyEntityType::Dealer,
            'sfa_id' => $gone->id,
            'tally_guid' => 'SHARED-GUID',
            'tally_name' => 'Gone',
        ]);
        TallyMapping::create([
            'entity_type' => TallyEntityType::Dealer,
            'sfa_id' => $keeper->id,
            'tally_guid' => 'OLD-STALE-GUID',
            'tally_name' => 'Keeper',
        ]);

        $result = $this->service()->applyPulledMasters(TallyEntityType::Dealer, [
            ['name' => 'Keeper', 'guid' => 'SHARED-GUID'],
        ]);

        $this->assertSame(1, $result['linked']);
        $this->assertSame(1, TallyMapping::where('entity_type', 'dealer')->count());
        $this->assertDatabaseHas('tally_mappings', [
            'sfa_id' => $keeper->id,
            'tally_guid' => 'SHARED-GUID',
            'deleted_at' => null,
        ]);
    }

    public function test_a_record_deleted_here_but_alive_in_tally_is_reported_not_reimported(): void
    {
        // Without this, every sync would mint another stub for the same
        // Tally record, forever.
        $dealer = Dealer::factory()->create(['name' => 'Dealer 1', 'tally_guid' => 'GUID-T1']);
        $dealer->delete();

        $result = $this->service()->applyPulledMasters(TallyEntityType::Dealer, [
            ['name' => 'Dealer 1', 'guid' => 'GUID-T1'],
        ]);

        $this->assertSame(['Dealer 1'], $result['deleted_here']);
        $this->assertEmpty($result['imported']);
        $this->assertSame(0, Dealer::count(), 'no live duplicate may be created');
        $this->assertSame(1, Dealer::withTrashed()->count());
    }

    public function test_one_pull_mixes_every_outcome_and_reports_each(): void
    {
        ProductCategory::factory()->create();
        Product::factory()->create(['name' => 'Linkable', 'tally_guid' => null]);
        Product::factory()->create(['name' => 'Conflicted', 'tally_guid' => 'HELD-GUID']);
        Product::factory()->create(['name' => 'Mapped', 'tally_guid' => 'SAME-GUID']);

        $result = $this->service()->applyPulledMasters(TallyEntityType::Product, [
            ['name' => 'Linkable', 'guid' => 'NEW-GUID'],
            ['name' => 'Conflicted', 'guid' => 'OTHER-GUID'],
            ['name' => 'Mapped', 'guid' => 'SAME-GUID'],
            ['name' => 'Only In Tally', 'guid' => 'TALLY-ONLY'],
        ]);

        $this->assertSame(1, $result['linked']);
        $this->assertSame(1, $result['already']);
        $this->assertSame(['Conflicted'], $result['conflicts']);
        $this->assertSame(['Only In Tally'], $result['imported']);
    }
}
