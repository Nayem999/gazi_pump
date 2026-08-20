<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_sales_executive_can_view_but_not_manage_categories(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('product-categories.index'))->assertOk();

        $response = $this->actingAs($executive)->post(route('product-categories.store'), [
            'name' => 'New Category',
            'code' => 'CAT-999',
        ]);
        $response->assertForbidden();
    }

    public function test_super_admin_can_create_a_category(): void
    {
        $response = $this->actingAs($this->superAdmin())->post(route('product-categories.store'), [
            'name' => 'Test Category',
            'code' => 'CAT-TEST',
            'status' => '1',
        ]);

        $response->assertRedirect(route('product-categories.index'));
        $this->assertDatabaseHas('product_categories', ['code' => 'CAT-TEST']);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $admin = $this->superAdmin();
        $category = ProductCategory::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)
            ->delete(route('product-categories.destroy', $category))
            ->assertForbidden();

        $this->assertDatabaseHas('product_categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_category_without_products_can_be_deleted(): void
    {
        $admin = $this->superAdmin();
        $category = ProductCategory::factory()->create();

        $this->actingAs($admin)
            ->delete(route('product-categories.destroy', $category))
            ->assertRedirect(route('product-categories.index'));

        $this->assertSoftDeleted('product_categories', ['id' => $category->id]);
    }
}
