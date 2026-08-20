<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Brochure;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrochureManagementTest extends TestCase
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
        $this->get(route('brochures.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_access(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('brochures.index'))->assertForbidden();
    }

    public function test_general_manager_can_create_a_brochure_with_a_pdf(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->generalManager())->post(route('brochures.store'), [
            'title' => 'Product Catalog 2026',
            'file' => UploadedFile::fake()->create('catalog.pdf', 500, 'application/pdf'),
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('brochures.index'));

        $brochure = Brochure::where('title', 'Product Catalog 2026')->firstOrFail();
        $this->assertNotNull($brochure->file);
        Storage::disk('public')->assertExists($brochure->file);
    }

    public function test_file_is_required_on_create_and_must_be_a_pdf(): void
    {
        $this->actingAs($this->generalManager())->post(route('brochures.store'), [
            'title' => 'Missing File',
        ])->assertSessionHasErrors('file');

        $this->actingAs($this->generalManager())->post(route('brochures.store'), [
            'title' => 'Wrong Type',
            'file' => UploadedFile::fake()->image('not-a-pdf.jpg'),
        ])->assertSessionHasErrors('file');
    }

    public function test_file_is_optional_on_update_and_replaces_the_old_one(): void
    {
        Storage::fake('public');

        $manager = $this->generalManager();
        $brochure = Brochure::factory()->create(['file' => 'brochures/old.pdf']);
        Storage::disk('public')->put('brochures/old.pdf', 'old content');

        $this->actingAs($manager)->put(route('brochures.update', $brochure), [
            'title' => 'Updated Brochure',
            'file' => UploadedFile::fake()->create('new.pdf', 500, 'application/pdf'),
            'is_published' => '1',
        ])->assertRedirect(route('brochures.index'));

        $brochure->refresh();
        $this->assertNotEquals('brochures/old.pdf', $brochure->file);
        Storage::disk('public')->assertMissing('brochures/old.pdf');
        Storage::disk('public')->assertExists($brochure->file);
    }

    public function test_general_manager_can_delete_and_restore_a_brochure(): void
    {
        $manager = $this->generalManager();
        $brochure = Brochure::factory()->create();

        $this->actingAs($manager)->delete(route('brochures.destroy', $brochure))->assertRedirect(route('brochures.index'));
        $this->assertSoftDeleted('brochures', ['id' => $brochure->id]);

        $this->actingAs($manager)->post(route('brochures.restore', $brochure->id))->assertRedirect(route('brochures.index'));
        $this->assertDatabaseHas('brochures', ['id' => $brochure->id, 'deleted_at' => null]);
    }

    public function test_toggle_status_flips_is_published(): void
    {
        $manager = $this->generalManager();
        $brochure = Brochure::factory()->create(['is_published' => true]);

        $this->actingAs($manager)->patch(route('brochures.toggle-status', $brochure))->assertRedirect();

        $this->assertDatabaseHas('brochures', ['id' => $brochure->id, 'is_published' => false]);
    }
}
