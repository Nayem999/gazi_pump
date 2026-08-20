<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Promotion;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PromotionManagementTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('promotions.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_access(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('promotions.index'))->assertForbidden();
    }

    public function test_general_manager_can_create_a_promotion_with_an_image(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->generalManager())->post(route('promotions.store'), [
            'title' => 'Monsoon Discount',
            'description' => '10% off all submersible pumps.',
            'image' => UploadedFile::fake()->image('promo.jpg'),
            'starts_at' => '2026-08-01',
            'ends_at' => '2026-08-31',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('promotions.index'));

        $promotion = Promotion::where('title', 'Monsoon Discount')->firstOrFail();
        $this->assertNotNull($promotion->image);
        Storage::disk('public')->assertExists($promotion->image);
    }

    public function test_end_date_must_not_be_before_start_date(): void
    {
        $this->actingAs($this->generalManager())->post(route('promotions.store'), [
            'title' => 'Bad Dates',
            'starts_at' => '2026-08-31',
            'ends_at' => '2026-08-01',
        ])->assertSessionHasErrors('ends_at');
    }

    public function test_general_manager_can_update_delete_and_restore_a_promotion(): void
    {
        $manager = $this->generalManager();
        $promotion = Promotion::factory()->create();

        $this->actingAs($manager)->put(route('promotions.update', $promotion), [
            'title' => 'Updated Promotion',
            'is_active' => '1',
        ])->assertRedirect(route('promotions.index'));
        $this->assertDatabaseHas('promotions', ['id' => $promotion->id, 'title' => 'Updated Promotion']);

        $this->actingAs($manager)->delete(route('promotions.destroy', $promotion))->assertRedirect(route('promotions.index'));
        $this->assertSoftDeleted('promotions', ['id' => $promotion->id]);

        $this->actingAs($manager)->post(route('promotions.restore', $promotion->id))->assertRedirect(route('promotions.index'));
        $this->assertDatabaseHas('promotions', ['id' => $promotion->id, 'deleted_at' => null]);
    }

    public function test_toggle_status_flips_is_active(): void
    {
        $manager = $this->generalManager();
        $promotion = Promotion::factory()->create(['is_active' => true]);

        $this->actingAs($manager)->patch(route('promotions.toggle-status', $promotion))->assertRedirect();

        $this->assertDatabaseHas('promotions', ['id' => $promotion->id, 'is_active' => false]);
    }
}
