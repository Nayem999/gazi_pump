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

    public function test_a_sub_category_can_be_created_under_a_top_level_category(): void
    {
        $admin = $this->superAdmin();
        $parent = ProductCategory::factory()->create();

        $response = $this->actingAs($admin)->post(route('product-categories.store'), [
            'name' => 'Sub Category',
            'code' => 'CAT-SUB',
            'parent_id' => $parent->id,
            'status' => '1',
        ]);

        $response->assertRedirect(route('product-categories.index'));
        $this->assertDatabaseHas('product_categories', ['code' => 'CAT-SUB', 'parent_id' => $parent->id]);
    }

    public function test_a_sub_category_cannot_itself_be_picked_as_a_parent(): void
    {
        $admin = $this->superAdmin();
        $parent = ProductCategory::factory()->create();
        $child = ProductCategory::factory()->create(['parent_id' => $parent->id]);

        $response = $this->actingAs($admin)->post(route('product-categories.store'), [
            'name' => 'Grandchild Category',
            'code' => 'CAT-GC',
            'parent_id' => $child->id,
            'status' => '1',
        ]);

        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseMissing('product_categories', ['code' => 'CAT-GC']);
    }

    public function test_a_category_cannot_be_set_as_its_own_parent(): void
    {
        $admin = $this->superAdmin();
        $category = ProductCategory::factory()->create();

        $response = $this->actingAs($admin)->put(route('product-categories.update', $category), [
            'name' => $category->name,
            'code' => $category->code,
            'parent_id' => $category->id,
            'status' => '1',
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_a_category_with_sub_categories_cannot_become_a_sub_category_itself(): void
    {
        $admin = $this->superAdmin();
        $parentA = ProductCategory::factory()->create();
        $parentB = ProductCategory::factory()->create();
        ProductCategory::factory()->create(['parent_id' => $parentA->id]);

        $response = $this->actingAs($admin)->put(route('product-categories.update', $parentA), [
            'name' => $parentA->name,
            'code' => $parentA->code,
            'parent_id' => $parentB->id,
            'status' => '1',
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_a_category_with_sub_categories_cannot_be_deleted(): void
    {
        $admin = $this->superAdmin();
        $parent = ProductCategory::factory()->create();
        ProductCategory::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($admin)
            ->delete(route('product-categories.destroy', $parent))
            ->assertForbidden();

        $this->assertDatabaseHas('product_categories', ['id' => $parent->id, 'deleted_at' => null]);
    }

    public function test_index_shows_the_parent_category_column(): void
    {
        $admin = $this->superAdmin();
        $parent = ProductCategory::factory()->create(['name' => 'Beverages']);
        ProductCategory::factory()->create(['name' => 'Soft Drinks', 'parent_id' => $parent->id]);

        $this->actingAs($admin)->get(route('product-categories.index'))
            ->assertOk()
            ->assertSee('Beverages')
            ->assertSee('Soft Drinks');
    }
}
