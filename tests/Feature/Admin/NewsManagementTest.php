<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsManagementTest extends TestCase
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
        $this->get(route('news.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_access(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('news.index'))->assertForbidden();
    }

    public function test_general_manager_can_create_an_article_with_a_cover_image(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->generalManager())->post(route('news.store'), [
            'title' => 'New Warranty Policy',
            'excerpt' => 'We updated our warranty terms.',
            'body' => 'Full details about the new warranty policy.',
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('news.index'));

        $article = News::where('title', 'New Warranty Policy')->firstOrFail();
        $this->assertNotNull($article->cover_image);
        Storage::disk('public')->assertExists($article->cover_image);
    }

    public function test_title_and_body_are_required(): void
    {
        $this->actingAs($this->generalManager())->post(route('news.store'), [])
            ->assertSessionHasErrors(['title', 'body']);
    }

    public function test_general_manager_can_update_delete_and_restore_an_article(): void
    {
        $manager = $this->generalManager();
        $article = News::factory()->create();

        $this->actingAs($manager)->put(route('news.update', $article), [
            'title' => 'Updated Title',
            'body' => $article->body,
            'is_published' => '1',
        ])->assertRedirect(route('news.index'));
        $this->assertDatabaseHas('news', ['id' => $article->id, 'title' => 'Updated Title']);

        $this->actingAs($manager)->delete(route('news.destroy', $article))->assertRedirect(route('news.index'));
        $this->assertSoftDeleted('news', ['id' => $article->id]);

        $this->actingAs($manager)->post(route('news.restore', $article->id))->assertRedirect(route('news.index'));
        $this->assertDatabaseHas('news', ['id' => $article->id, 'deleted_at' => null]);
    }

    public function test_toggle_status_flips_is_published(): void
    {
        $manager = $this->generalManager();
        $article = News::factory()->create(['is_published' => true]);

        $this->actingAs($manager)->patch(route('news.toggle-status', $article))->assertRedirect();

        $this->assertDatabaseHas('news', ['id' => $article->id, 'is_published' => false]);
    }
}
