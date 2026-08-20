<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_can_view_but_not_create_products(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('products.index'))->assertOk();

        $category = ProductCategory::factory()->create();

        $this->actingAs($executive)->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'New Product',
            'sku' => 'SKU-999',
            'price' => '100',
        ])->assertForbidden();
    }

    public function test_super_admin_can_create_a_product_with_an_image(): void
    {
        Storage::fake('public');

        $category = ProductCategory::factory()->create();

        $response = $this->actingAs($this->superAdmin())->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Test Pump',
            'sku' => 'SKU-TEST-01',
            'price' => '1500.50',
            'image' => UploadedFile::fake()->image('pump.jpg'),
            'status' => '1',
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'SKU-TEST-01')->firstOrFail();
        $this->assertEquals(1500.50, (float) $product->price);
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_price_must_be_a_non_negative_number(): void
    {
        $category = ProductCategory::factory()->create();

        $response = $this->actingAs($this->superAdmin())->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Test Pump',
            'sku' => 'SKU-TEST-02',
            'price' => '-10',
        ]);

        $response->assertSessionHasErrors('price');
    }

    public function test_sku_must_be_unique(): void
    {
        $existing = Product::factory()->create(['sku' => 'SKU-DUP']);

        $response = $this->actingAs($this->superAdmin())->post(route('products.store'), [
            'category_id' => $existing->category_id,
            'name' => 'Duplicate SKU Product',
            'sku' => 'SKU-DUP',
            'price' => '100',
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_super_admin_can_update_and_delete_a_product(): void
    {
        $admin = $this->superAdmin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->put(route('products.update', $product), [
            'category_id' => $product->category_id,
            'name' => 'Renamed Product',
            'sku' => $product->sku,
            'price' => '999.99',
            'status' => '1',
        ])->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Renamed Product']);

        $this->actingAs($admin)->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
