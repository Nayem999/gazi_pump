<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Collection Entries are retired for every role but Super Admin (see the
 * "version 1" Achievement pivot) — `collection-entries.*`/
 * `menu.collection-entries`/`api.collection-entries.*` are no longer
 * assigned to General Manager, Sales/Area/Territory Manager, or Sales
 * Executive. The underlying CollectionEntryService/Policy business logic
 * (territory scoping, OTP verification, cheque lifecycle, overpayment
 * tolerance, approve/reject forward-only) is still real code, still worth
 * covering — those cases now run as Super Admin, the only role left with
 * access, rather than being deleted.
 */
class CollectionEntryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function generalManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('General Manager');

        return $user;
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    /**
     * Sends an OTP and returns the otp_id/otp_code pair to merge into a
     * store() payload — a collection can no longer be recorded without one.
     * Demo mode always issues the fixed code 123456.
     *
     * @return array{otp_id: int, otp_code: string}
     */
    private function sendOtp(User $actor, int $dealerId, float $amount, string $paymentMethod = 'cash'): array
    {
        $response = $this->actingAs($actor)->postJson(route('collection-entries.send-otp'), [
            'dealer_id' => $dealerId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
        ]);

        return ['otp_id' => $response->json('otp_id'), 'otp_code' => '123456'];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('collection-entries.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_no_longer_has_any_web_access(): void
    {
        $executive = $this->executive();
        $entry = CollectionEntry::factory()->create(['user_id' => $executive->id]);

        $this->actingAs($executive)->get(route('collection-entries.index'))->assertForbidden();
        $this->actingAs($executive)->get(route('collection-entries.create'))->assertForbidden();
        $this->actingAs($executive)->get(route('collection-entries.show', $entry))->assertForbidden();
    }

    public function test_general_manager_and_territory_manager_no_longer_have_any_web_access(): void
    {
        foreach (['General Manager', 'Territory Manager', 'Sales Manager', 'Area Manager'] as $role) {
            $manager = User::factory()->create();
            $manager->assignRole($role);

            $this->actingAs($manager)->get(route('collection-entries.index'))->assertForbidden();
            $this->actingAs($manager)->get(route('collection-entries.create'))->assertForbidden();
        }
    }

    public function test_a_territory_scoped_viewer_can_only_pick_a_dealer_in_their_own_territory_on_the_collection_form(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $dealerA = Dealer::factory()->create(['territory_id' => $territoryA->id]);
        $dealerB = Dealer::factory()->create(['territory_id' => $territoryB->id]);

        // collection-entries.add is now Super Admin only — territories can
        // still be assigned to any role via the Users form, so this
        // exercises a regionally-scoped Super Admin.
        $admin = $this->superAdmin();
        $admin->territories()->attach($territoryA);

        $response = $this->actingAs($admin)->get(route('collection-entries.create'));

        $response->assertOk()->assertSee($dealerA->name)->assertDontSee($dealerB->name);
    }

    public function test_recording_a_collection_for_a_dealer_outside_the_viewers_territory_is_rejected(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $dealerB = Dealer::factory()->create(['territory_id' => $territoryB->id]);

        $admin = $this->superAdmin();
        $admin->territories()->attach($territoryA);
        $executive = $this->executive();

        $this->actingAs($admin)->postJson(route('collection-entries.send-otp'), [
            'dealer_id' => $dealerB->id,
            'amount' => 500,
            'payment_method' => 'cash',
        ])->assertJsonValidationErrors('dealer_id');

        $response = $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealerB->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 500,
            'payment_method' => 'cash',
            'otp_id' => 1,
            'otp_code' => '123456',
        ]);

        $response->assertSessionHasErrors('dealer_id');
        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_super_admin_can_view_and_record_a_collection(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->actingAs($admin)->get(route('collection-entries.index'))->assertOk();

        $response = $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'cash',
            ...$this->sendOtp($admin, $dealer->id, 600),
        ]);

        $response->assertRedirect(route('collection-entries.index'));
        $this->assertDatabaseHas('collection_entries', [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'amount' => 600,
        ]);
    }

    public function test_recording_a_cheque_collection_without_an_image_is_rejected(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'cheque',
        ])->assertSessionHasErrors('cheque_image');

        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_a_cheque_image_can_be_uploaded_when_recording_a_cheque_collection(): void
    {
        Storage::fake('public');

        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $response = $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'cheque',
            'reference_no' => 'CHQ-001',
            'cheque_image' => UploadedFile::fake()->image('cheque.jpg'),
            ...$this->sendOtp($admin, $dealer->id, 600, 'cheque'),
        ]);

        $response->assertRedirect(route('collection-entries.index'));

        $entry = CollectionEntry::where('reference_no', 'CHQ-001')->firstOrFail();
        Storage::disk('public')->assertExists($entry->cheque_image);
        $this->assertNotNull($entry->chequeImageUrl());
    }

    public function test_a_bank_transfer_collection_requires_a_transaction_id(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'bank_transfer',
        ])->assertSessionHasErrors('reference_no');

        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_a_duplicate_bank_transaction_id_is_rejected(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 10000]);
        CollectionEntry::factory()->create([
            'dealer_id' => $dealer->id,
            'amount' => 1000,
            'payment_method' => 'bank_transfer',
            'reference_no' => 'TXN-DUP-001',
        ]);

        $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'mobile_banking',
            'reference_no' => 'TXN-DUP-001',
        ])->assertSessionHasErrors('reference_no');

        $this->assertDatabaseCount('collection_entries', 1);
    }

    public function test_the_same_reference_no_is_allowed_across_cheque_and_bank_entries(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 10000]);
        CollectionEntry::factory()->create([
            'dealer_id' => $dealer->id,
            'amount' => 1000,
            'payment_method' => 'cheque',
            'reference_no' => 'SHARED-001',
        ]);

        // A cheque number and a bank/MFS transaction ID are different
        // identifier spaces — the duplicate check only applies within
        // bank_transfer/mobile_banking entries, so this must succeed.
        $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'mobile_banking',
            'reference_no' => 'SHARED-001',
            ...$this->sendOtp($admin, $dealer->id, 600, 'mobile_banking'),
        ])->assertRedirect(route('collection-entries.index'));

        $this->assertDatabaseCount('collection_entries', 2);
    }

    public function test_a_new_cheque_collection_starts_at_the_collected_status(): void
    {
        Storage::fake('public');

        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'cheque',
            'cheque_image' => UploadedFile::fake()->image('cheque.jpg'),
            ...$this->sendOtp($admin, $dealer->id, 600, 'cheque'),
        ]);

        $this->assertDatabaseHas('collection_entries', ['dealer_id' => $dealer->id, 'cheque_status' => 'collected']);
    }

    public function test_a_cheque_status_can_advance_through_its_lifecycle(): void
    {
        $admin = $this->superAdmin();
        $entry = CollectionEntry::factory()->create(['payment_method' => 'cheque', 'cheque_status' => 'collected']);

        $this->actingAs($admin)->patch(route('collection-entries.cheque-status', $entry), ['cheque_status' => 'submitted'])
            ->assertRedirect();
        $this->assertDatabaseHas('collection_entries', ['id' => $entry->id, 'cheque_status' => 'submitted']);

        $this->actingAs($admin)->patch(route('collection-entries.cheque-status', $entry), ['cheque_status' => 'deposited'])
            ->assertRedirect();
        $this->assertDatabaseHas('collection_entries', ['id' => $entry->id, 'cheque_status' => 'deposited']);

        $this->actingAs($admin)->patch(route('collection-entries.cheque-status', $entry), ['cheque_status' => 'cleared'])
            ->assertRedirect();
        $this->assertDatabaseHas('collection_entries', ['id' => $entry->id, 'cheque_status' => 'cleared']);
    }

    public function test_a_cheque_status_cannot_skip_ahead(): void
    {
        $admin = $this->superAdmin();
        $entry = CollectionEntry::factory()->create(['payment_method' => 'cheque', 'cheque_status' => 'collected']);

        $this->actingAs($admin)->patch(route('collection-entries.cheque-status', $entry), ['cheque_status' => 'cleared'])
            ->assertSessionHasErrors('cheque_status');

        $this->assertDatabaseHas('collection_entries', ['id' => $entry->id, 'cheque_status' => 'collected']);
    }

    public function test_a_cleared_cheque_has_no_further_transitions(): void
    {
        $admin = $this->superAdmin();
        $entry = CollectionEntry::factory()->create(['payment_method' => 'cheque', 'cheque_status' => 'cleared']);

        $this->actingAs($admin)->patch(route('collection-entries.cheque-status', $entry), ['cheque_status' => 'bounced'])
            ->assertSessionHasErrors('cheque_status');
    }

    public function test_cheque_status_cannot_be_changed_on_a_non_cheque_entry(): void
    {
        $admin = $this->superAdmin();
        $entry = CollectionEntry::factory()->create(['payment_method' => 'cash', 'cheque_status' => null]);

        $this->actingAs($admin)->patch(route('collection-entries.cheque-status', $entry), ['cheque_status' => 'submitted'])
            ->assertSessionHasErrors('cheque_status');
    }

    public function test_updating_a_cheque_image_deletes_the_previous_file(): void
    {
        Storage::fake('public');

        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);
        $entry = CollectionEntry::factory()->create([
            'dealer_id' => $dealer->id,
            'amount' => 100,
            'payment_method' => 'cheque',
            'cheque_image' => UploadedFile::fake()->image('old.jpg')->store('collection-entries', 'public'),
        ]);
        $oldPath = $entry->cheque_image;

        $this->actingAs($admin)->put(route('collection-entries.update', $entry), [
            'user_id' => $entry->user_id,
            'dealer_id' => $dealer->id,
            'collection_date' => $entry->collection_date->toDateString(),
            'amount' => 100,
            'payment_method' => 'cheque',
            'cheque_image' => UploadedFile::fake()->image('new.jpg'),
        ])->assertRedirect(route('collection-entries.index'));

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($entry->fresh()->cheque_image);
    }

    public function test_the_territory_filter_only_returns_collections_for_dealers_in_that_territory(): void
    {
        $admin = $this->superAdmin();
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $dealerA = Dealer::factory()->create(['territory_id' => $territoryA->id]);
        $dealerB = Dealer::factory()->create(['territory_id' => $territoryB->id]);

        CollectionEntry::factory()->create(['dealer_id' => $dealerA->id]);
        CollectionEntry::factory()->create(['dealer_id' => $dealerB->id]);

        $response = $this->actingAs($admin)->get(route('collection-entries.index', ['territory_id' => $territoryA->id]));

        $response->assertOk();
        $response->assertSee($dealerA->name);
        $response->assertDontSee($dealerB->name);
    }

    public function test_a_collection_beyond_the_overpayment_tolerance_is_rejected(): void
    {
        config(['sfa.collections.overpayment_tolerance_percent' => 10]);
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $response = $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 2000,
            'payment_method' => 'cash',
            ...$this->sendOtp($admin, $dealer->id, 2000),
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_super_admin_can_update_a_collection_entry(): void
    {
        Storage::fake('public');

        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);
        $collectionEntry = CollectionEntry::factory()->create(['dealer_id' => $dealer->id, 'amount' => 300]);

        $this->actingAs($admin)->put(route('collection-entries.update', $collectionEntry), [
            'user_id' => $collectionEntry->user_id,
            'dealer_id' => $dealer->id,
            'collection_date' => $collectionEntry->collection_date->toDateString(),
            'amount' => 500,
            'payment_method' => 'cheque',
            'reference_no' => 'CHK-1001',
            'cheque_image' => UploadedFile::fake()->image('cheque.jpg'),
        ])->assertRedirect(route('collection-entries.index'));

        $this->assertDatabaseHas('collection_entries', ['id' => $collectionEntry->id, 'amount' => 500, 'reference_no' => 'CHK-1001']);
    }

    public function test_switching_an_existing_entry_to_cheque_requires_an_image(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);
        $collectionEntry = CollectionEntry::factory()->create(['dealer_id' => $dealer->id, 'amount' => 300, 'payment_method' => 'cash']);

        $this->actingAs($admin)->put(route('collection-entries.update', $collectionEntry), [
            'user_id' => $collectionEntry->user_id,
            'dealer_id' => $dealer->id,
            'collection_date' => $collectionEntry->collection_date->toDateString(),
            'amount' => 300,
            'payment_method' => 'cheque',
        ])->assertSessionHasErrors('cheque_image');
    }

    public function test_editing_an_existing_cheque_collection_without_touching_the_image_keeps_it(): void
    {
        Storage::fake('public');

        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);
        $collectionEntry = CollectionEntry::factory()->create([
            'dealer_id' => $dealer->id,
            'amount' => 300,
            'payment_method' => 'cheque',
            'cheque_image' => UploadedFile::fake()->image('existing.jpg')->store('collection-entries', 'public'),
        ]);
        $existingPath = $collectionEntry->cheque_image;

        $this->actingAs($admin)->put(route('collection-entries.update', $collectionEntry), [
            'user_id' => $collectionEntry->user_id,
            'dealer_id' => $dealer->id,
            'collection_date' => $collectionEntry->collection_date->toDateString(),
            'amount' => 300,
            'payment_method' => 'cheque',
            'remarks' => 'Updated remarks only',
        ])->assertRedirect(route('collection-entries.index'));

        $this->assertSame($existingPath, $collectionEntry->fresh()->cheque_image);
        Storage::disk('public')->assertExists($existingPath);
    }

    public function test_general_manager_cannot_delete_a_collection_entry(): void
    {
        $manager = $this->generalManager();
        $collectionEntry = CollectionEntry::factory()->create();

        $this->actingAs($manager)->delete(route('collection-entries.destroy', $collectionEntry))->assertForbidden();
    }

    public function test_super_admin_can_delete_and_restore_a_collection_entry(): void
    {
        $admin = $this->superAdmin();
        $collectionEntry = CollectionEntry::factory()->create();

        $this->actingAs($admin)->delete(route('collection-entries.destroy', $collectionEntry))
            ->assertRedirect(route('collection-entries.index'));
        $this->assertSoftDeleted('collection_entries', ['id' => $collectionEntry->id]);

        $this->actingAs($admin)->post(route('collection-entries.restore', $collectionEntry->id))
            ->assertRedirect(route('collection-entries.index'));
        $this->assertDatabaseHas('collection_entries', ['id' => $collectionEntry->id, 'deleted_at' => null]);
    }

    public function test_the_detail_pdf_renders_with_a_cheque_image_present(): void
    {
        Storage::fake('public');

        $admin = $this->superAdmin();
        $collectionEntry = CollectionEntry::factory()->create([
            'payment_method' => 'cheque',
            'cheque_image' => UploadedFile::fake()->image('cheque.jpg')->store('collection-entries', 'public'),
        ]);

        $this->actingAs($admin)->get(route('collection-entries.download-pdf', $collectionEntry))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_super_admin_can_send_an_otp_and_gets_a_demo_code_back(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('collection-entries.send-otp'), [
            'dealer_id' => $dealer->id,
            'amount' => 600,
            'payment_method' => 'cash',
        ]);

        $response->assertOk();
        $this->assertFalse($response->json('sent'));
        $this->assertSame('123456', $response->json('demo_code'));
        $this->assertDatabaseHas('collection_otps', ['dealer_id' => $dealer->id, 'user_id' => $admin->id]);
    }

    public function test_a_collection_can_be_recorded_with_a_valid_otp(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $otpResponse = $this->actingAs($admin)->postJson(route('collection-entries.send-otp'), [
            'dealer_id' => $dealer->id,
            'amount' => 600,
            'payment_method' => 'cash',
        ]);

        $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'cash',
            'otp_id' => $otpResponse->json('otp_id'),
            'otp_code' => $otpResponse->json('demo_code'),
        ])->assertRedirect(route('collection-entries.index'));

        $entry = CollectionEntry::where('dealer_id', $dealer->id)->firstOrFail();
        $this->assertNotNull($entry->otp_verified_at);
    }

    public function test_a_collection_is_rejected_when_the_otp_code_is_wrong(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $otpResponse = $this->actingAs($admin)->postJson(route('collection-entries.send-otp'), [
            'dealer_id' => $dealer->id,
            'amount' => 600,
            'payment_method' => 'cash',
        ]);

        $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'cash',
            'otp_id' => $otpResponse->json('otp_id'),
            'otp_code' => '000000',
        ])->assertSessionHasErrors('otp');

        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_an_otp_sent_by_one_admin_cannot_be_used_by_another(): void
    {
        $adminA = $this->superAdmin();
        $adminB = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $otpResponse = $this->actingAs($adminA)->postJson(route('collection-entries.send-otp'), [
            'dealer_id' => $dealer->id,
            'amount' => 600,
            'payment_method' => 'cash',
        ]);

        $this->actingAs($adminB)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'cash',
            'otp_id' => $otpResponse->json('otp_id'),
            'otp_code' => $otpResponse->json('demo_code'),
        ])->assertSessionHasErrors('otp');

        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_the_print_report_renders_with_a_cheque_image_present(): void
    {
        Storage::fake('public');

        $admin = $this->superAdmin();
        CollectionEntry::factory()->create([
            'payment_method' => 'cheque',
            'cheque_image' => UploadedFile::fake()->image('cheque.jpg')->store('collection-entries', 'public'),
        ]);

        $this->actingAs($admin)->get(route('collection-entries.print'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_a_new_collection_starts_pending(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->actingAs($admin)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'cash',
            ...$this->sendOtp($admin, $dealer->id, 600),
        ]);

        $this->assertDatabaseHas('collection_entries', ['dealer_id' => $dealer->id, 'status' => 'pending']);
    }

    public function test_super_admin_can_approve_a_pending_collection(): void
    {
        $admin = $this->superAdmin();
        $entry = CollectionEntry::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)->patch(route('collection-entries.approve', $entry))->assertRedirect();

        $this->assertDatabaseHas('collection_entries', ['id' => $entry->id, 'status' => 'approved', 'approved_by' => $admin->id]);
    }

    public function test_super_admin_can_reject_a_pending_collection(): void
    {
        $admin = $this->superAdmin();
        $entry = CollectionEntry::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)->patch(route('collection-entries.reject', $entry))->assertRedirect();

        $this->assertDatabaseHas('collection_entries', ['id' => $entry->id, 'status' => 'rejected', 'approved_by' => $admin->id]);
    }

    public function test_an_already_rejected_collection_cannot_be_approved(): void
    {
        $admin = $this->superAdmin();
        $entry = CollectionEntry::factory()->create(['status' => 'rejected', 'approved_by' => $admin->id, 'approved_at' => now()]);

        $this->actingAs($admin)->patch(route('collection-entries.approve', $entry))->assertSessionHasErrors('status');
    }

    public function test_a_territory_manager_cannot_approve_a_collection(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $entry = CollectionEntry::factory()->create(['status' => 'pending']);

        $this->actingAs($manager)->patch(route('collection-entries.approve', $entry))->assertForbidden();
    }

    public function test_the_approval_filter_only_returns_collections_with_that_status(): void
    {
        $admin = $this->superAdmin();
        $pending = CollectionEntry::factory()->create(['status' => 'pending']);
        $approved = CollectionEntry::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($admin)->get(route('collection-entries.index', ['status' => 'approved']));

        $response->assertOk();
        $response->assertSee($approved->dealer->name);
        $response->assertDontSee($pending->dealer->name);
    }
}
