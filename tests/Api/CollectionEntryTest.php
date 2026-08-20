<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\CollectionEntry;
use App\Models\Customer;
use App\Models\SalesEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

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

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/collection-entries', [])->assertStatus(401);
    }

    public function test_sales_executive_can_record_a_collection(): void
    {
        $executive = $this->executive();
        $customer = Customer::factory()->create();
        SalesEntry::factory()->create(['customer_id' => $customer->id, 'total_amount' => 1000]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/collection-entries', [
                'customer_id' => $customer->id,
                'amount' => 600,
                'payment_method' => 'mobile_banking',
                'reference_no' => 'TXN-9001',
            ]);

        $response->assertStatus(201)->assertJsonPath('success', true);

        $entry = CollectionEntry::where('user_id', $executive->id)->firstOrFail();
        $this->assertSame('600.00', (string) $entry->amount);
        $this->assertSame('mobile_banking', $entry->payment_method->value);
    }

    public function test_a_collection_beyond_the_overpayment_tolerance_is_rejected(): void
    {
        config(['sfa.collections.overpayment_tolerance_percent' => 10]);
        $executive = $this->executive();
        $customer = Customer::factory()->create();
        SalesEntry::factory()->create(['customer_id' => $customer->id, 'total_amount' => 1000]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/collection-entries', [
                'customer_id' => $customer->id,
                'amount' => 2000,
                'payment_method' => 'cash',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_index_only_returns_the_authenticated_users_own_collections(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();

        CollectionEntry::factory()->create(['user_id' => $executive->id, 'collection_date' => Carbon::yesterday()->toDateString()]);
        CollectionEntry::factory()->create(['user_id' => $otherExecutive->id, 'collection_date' => Carbon::yesterday()->toDateString()]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/collection-entries')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
