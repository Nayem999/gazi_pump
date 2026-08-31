<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesTeam;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
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

    public function test_products_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/products')->assertStatus(401);
    }

    public function test_sales_executive_can_list_active_products(): void
    {
        Product::factory()->count(3)->create(['status' => true]);
        Product::factory()->create(['status' => false]);

        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        $categoryA = ProductCategory::factory()->create();
        $categoryB = ProductCategory::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $categoryA->id]);
        Product::factory()->create(['category_id' => $categoryB->id]);

        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson("/api/v1/products?category_id={$categoryA->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_filtering_by_a_parent_category_also_includes_its_sub_categories_products(): void
    {
        $parent = ProductCategory::factory()->create();
        $child = ProductCategory::factory()->create(['parent_id' => $parent->id]);
        $other = ProductCategory::factory()->create();

        Product::factory()->create(['category_id' => $parent->id]);
        Product::factory()->create(['category_id' => $child->id]);
        Product::factory()->create(['category_id' => $other->id]);

        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson("/api/v1/products?category_id={$parent->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_products_category_includes_its_parent_when_it_is_a_sub_category(): void
    {
        $parent = ProductCategory::factory()->create(['name' => 'Beverages']);
        $child = ProductCategory::factory()->create(['name' => 'Soft Drinks', 'parent_id' => $parent->id]);
        $product = Product::factory()->create(['category_id' => $child->id]);

        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson("/api/v1/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.category.name', 'Soft Drinks')
            ->assertJsonPath('data.category.parent.name', 'Beverages');
    }

    public function test_an_executive_under_a_team_only_sees_that_teams_own_products(): void
    {
        $teamA = SalesTeam::factory()->create();
        $teamB = SalesTeam::factory()->create();

        Product::factory()->create(['sales_team_id' => $teamA->id]);
        Product::factory()->create(['sales_team_id' => $teamB->id]);
        Product::factory()->create(['sales_team_id' => null]);

        $executive = User::factory()->create(['sales_team_id' => $teamA->id]);
        $executive->assignRole('Sales Executive');

        // Strictly teamA's own product — the other team's and the
        // team-less one are both excluded.
        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_show_returns_a_single_product_with_category(): void
    {
        $product = Product::factory()->create();
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson("/api/v1/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.sku', $product->sku)
            ->assertJsonPath('data.category.id', $product->category_id);
    }
}
