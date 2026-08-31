<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\CollectionEntry;
use App\Models\CollectionOtp;
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
 * Collection Entry mobile self-service is retired for Sales Executive (see
 * the "version 1" Achievement pivot) — `api.collection-entries.add`/`.view`
 * are no longer assigned to any role but Super Admin, so a plain Sales
 * Executive now gets 403 on every one of these endpoints. The underlying
 * CollectionEntryService/OTP business logic is still real code, still worth
 * covering — those cases now run as Super Admin, the only role left with
 * access, rather than being deleted.
 */
class CollectionEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function tokenFor(User $user): string
    {
        return $user->createToken('phpunit')->plainTextToken;
    }

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    /**
     * Sends an OTP and returns the otp_id/otp_code pair to merge into a
     * store() payload — a collection can no longer be recorded without one.
     * Demo mode always issues the fixed code 123456.
     *
     * @return array{otp_id: int, otp_code: string}
     */
    private function sendOtp(User $user, int $dealerId, float $amount, string $paymentMethod = 'cash'): array
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->postJson('/api/v1/collection-entries/send-otp', [
                'dealer_id' => $dealerId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
            ]);

        return ['otp_id' => $response->json('data.otp_id'), 'otp_code' => '123456'];
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/collection-entries', [])->assertStatus(401);
    }

    public function test_a_sales_executive_can_no_longer_record_a_mobile_collection(): void
    {
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();

        // send-otp and store() are gated by api.collection-entries.add,
        // which is now retired; index() has no permission gate at all
        // (pre-existing — it always scoped by the caller's own user_id
        // instead), so it stays reachable.
        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/collection-entries/send-otp', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
            ])
            ->assertForbidden();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
            ])
            ->assertForbidden();
    }

    public function test_an_authorized_user_can_record_a_collection(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'mobile_banking',
                'reference_no' => 'TXN-9001',
                ...$this->sendOtp($admin, $dealer->id, 600, 'mobile_banking'),
            ]);

        $response->assertStatus(201)->assertJsonPath('success', true);

        $entry = CollectionEntry::where('user_id', $admin->id)->firstOrFail();
        $this->assertSame('600.00', (string) $entry->amount);
        $this->assertSame('mobile_banking', $entry->payment_method->value);
    }

    public function test_recording_a_cheque_collection_without_an_image_is_rejected(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cheque',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('cheque_image');
    }

    public function test_sales_executive_can_upload_a_cheque_image_when_recording_a_cheque_collection(): void
    {
        Storage::fake('public');

        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cheque',
                'reference_no' => 'CHQ-9001',
                'cheque_image' => UploadedFile::fake()->image('cheque.jpg'),
                ...$this->sendOtp($admin, $dealer->id, 600, 'cheque'),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.cheque_image_url', fn ($url) => is_string($url));

        $entry = CollectionEntry::where('reference_no', 'CHQ-9001')->firstOrFail();
        Storage::disk('public')->assertExists($entry->cheque_image);
    }

    public function test_a_collection_beyond_the_overpayment_tolerance_is_rejected(): void
    {
        config(['sfa.collections.overpayment_tolerance_percent' => 10]);
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 2000,
                'payment_method' => 'cash',
                ...$this->sendOtp($admin, $dealer->id, 2000),
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_sending_an_otp_for_a_dealer_outside_the_executives_territory_is_rejected(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $admin = User::factory()->inTerritory($territoryA)->create();
        $admin->assignRole('Super Admin');
        $dealer = Dealer::factory()->create(['territory_id' => $territoryB->id]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries/send-otp', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('dealer_id');
    }

    public function test_recording_a_collection_for_a_dealer_outside_the_executives_territory_is_rejected(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $admin = User::factory()->inTerritory($territoryA)->create();
        $admin->assignRole('Super Admin');
        $dealer = Dealer::factory()->create(['territory_id' => $territoryB->id]);
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
                'otp_id' => 1,
                'otp_code' => '123456',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('dealer_id');

        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_index_only_returns_the_authenticated_users_own_collections(): void
    {
        $admin = $this->superAdmin();
        $otherAdmin = $this->superAdmin();

        CollectionEntry::factory()->create(['user_id' => $admin->id, 'collection_date' => Carbon::yesterday()->toDateString()]);
        CollectionEntry::factory()->create(['user_id' => $otherAdmin->id, 'collection_date' => Carbon::yesterday()->toDateString()]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->getJson('/api/v1/collection-entries')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_send_otp_returns_a_demo_code_when_no_sms_gateway_is_configured(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries/send-otp', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
            ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertFalse($response->json('data.sent'));
        $this->assertSame('123456', $response->json('data.demo_code'));
        $this->assertDatabaseCount('collection_otps', 1);
    }

    public function test_a_collection_can_be_submitted_with_a_valid_otp(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $otpResponse = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries/send-otp', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
            ]);
        $otpId = $otpResponse->json('data.otp_id');
        $code = $otpResponse->json('data.demo_code');

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
                'otp_id' => $otpId,
                'otp_code' => $code,
            ]);

        $response->assertStatus(201)->assertJsonPath('success', true);

        $entry = CollectionEntry::where('dealer_id', $dealer->id)->firstOrFail();
        $this->assertNotNull($entry->otp_verified_at);
        $this->assertNotNull(CollectionOtp::find($otpId)->verified_at);
    }

    public function test_a_collection_is_rejected_when_the_otp_code_is_wrong(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $otpResponse = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries/send-otp', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
            ]);
        $otpId = $otpResponse->json('data.otp_id');

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
                'otp_id' => $otpId,
                'otp_code' => '000000',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('otp');

        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_a_collection_is_rejected_when_the_otp_amount_does_not_match(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $otpResponse = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries/send-otp', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
            ]);
        $otpId = $otpResponse->json('data.otp_id');
        $code = $otpResponse->json('data.demo_code');

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 700,
                'payment_method' => 'cash',
                'otp_id' => $otpId,
                'otp_code' => $code,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('otp');

        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_an_expired_otp_is_rejected(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $otpResponse = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries/send-otp', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
            ]);
        $otpId = $otpResponse->json('data.otp_id');
        $code = $otpResponse->json('data.demo_code');
        CollectionOtp::find($otpId)->update(['expires_at' => now()->subMinute()]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
                'otp_id' => $otpId,
                'otp_code' => $code,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('otp');

        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_a_collection_cannot_be_submitted_without_an_otp(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['otp_id', 'otp_code']);

        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_a_recorded_collection_reports_a_pending_status(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/collection-entries', [
                'dealer_id' => $dealer->id,
                'amount' => 600,
                'payment_method' => 'cash',
                ...$this->sendOtp($admin, $dealer->id, 600),
            ]);

        $response->assertJsonPath('data.status', 'pending')->assertJsonPath('data.status_label', 'Pending');
    }

    public function test_index_can_be_filtered_by_approval_status(): void
    {
        $admin = $this->superAdmin();
        CollectionEntry::factory()->create(['user_id' => $admin->id, 'status' => 'pending']);
        CollectionEntry::factory()->create(['user_id' => $admin->id, 'status' => 'approved']);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->getJson('/api/v1/collection-entries?status=approved');

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.status', 'approved');
    }
}
