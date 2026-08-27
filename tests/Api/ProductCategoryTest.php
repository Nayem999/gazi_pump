<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function authHeader(): array
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');
        $token = $user->createToken('phpunit')->plainTextToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_product_categories_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/product-categories')->assertStatus(401);
    }

    public function test_it_lists_every_active_category_including_sub_categories(): void
    {
        $parent = ProductCategory::factory()->create(['status' => true]);
        ProductCategory::factory()->create(['parent_id' => $parent->id, 'status' => true]);
        ProductCategory::factory()->create(['status' => false]);

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/product-categories')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_it_can_be_filtered_to_top_level_categories_only(): void
    {
        $parent = ProductCategory::factory()->create();
        ProductCategory::factory()->create(['parent_id' => $parent->id]);

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/product-categories?parent_id=none')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $parent->id);
    }

    public function test_it_can_be_filtered_to_the_sub_categories_of_a_parent(): void
    {
        $parentA = ProductCategory::factory()->create();
        $parentB = ProductCategory::factory()->create();
        $child = ProductCategory::factory()->create(['parent_id' => $parentA->id]);
        ProductCategory::factory()->create(['parent_id' => $parentB->id]);

        $this->withHeaders($this->authHeader())
            ->getJson("/api/v1/product-categories?parent_id={$parentA->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $child->id);
    }
}
