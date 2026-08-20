<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementManagementTest extends TestCase
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

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('announcements.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_cannot_view_or_create_announcements(): void
    {
        $executive = $this->executive();

        $this->actingAs($executive)->get(route('announcements.index'))->assertForbidden();
        $this->actingAs($executive)->get(route('announcements.create'))->assertForbidden();
    }

    public function test_general_manager_can_view_and_send_an_announcement_to_everyone(): void
    {
        $manager = $this->generalManager();
        User::factory()->count(2)->create();

        $this->actingAs($manager)->get(route('announcements.index'))->assertOk();

        $response = $this->actingAs($manager)->post(route('announcements.store'), [
            'title' => 'System Maintenance',
            'message' => 'The system will be down for maintenance tonight.',
            'audience' => 'all',
        ]);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', ['title' => 'System Maintenance', 'sent_by' => $manager->id]);
    }

    public function test_sending_to_a_role_requires_the_role_field(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->post(route('announcements.store'), [
            'title' => 'For a role',
            'message' => 'Message body',
            'audience' => 'role',
        ])->assertSessionHasErrors('audience_role');
    }

    public function test_general_manager_can_delete_and_restore_an_announcement(): void
    {
        $manager = $this->generalManager();
        $announcement = Announcement::factory()->create(['sent_by' => $manager->id]);

        $this->actingAs($manager)->delete(route('announcements.destroy', $announcement))->assertRedirect();
        $this->assertSoftDeleted($announcement);

        $this->actingAs($manager)->post(route('announcements.restore', $announcement->id))->assertRedirect();
        $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'deleted_at' => null]);
    }
}
