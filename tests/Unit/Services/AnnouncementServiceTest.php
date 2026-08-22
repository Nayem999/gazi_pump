<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\AnnouncementAudience;
use App\Models\Territory;
use App\Models\User;
use App\Services\AnnouncementService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // resolveRecipients(Role) calls User::role(...), which needs the
        // role to exist.
        $this->seed(RolePermissionSeeder::class);
    }

    private function service(): AnnouncementService
    {
        return app(AnnouncementService::class);
    }

    public function test_sending_to_everyone_notifies_every_active_user(): void
    {
        $sender = User::factory()->create();
        User::factory()->count(3)->create(['status' => true]);
        User::factory()->create(['status' => false]);

        $announcement = $this->service()->send($sender, [
            'title' => 'Test',
            'message' => 'Hello everyone',
            'audience' => AnnouncementAudience::All->value,
            'audience_role' => null,
            'audience_territory_id' => null,
            'audience_user_id' => null,
        ]);

        // sender + 3 active users = 4 (the inactive one is excluded).
        $this->assertSame(4, $announcement->recipient_count);
        $this->assertSame($sender->id, $announcement->sent_by);
    }

    public function test_sending_to_a_role_only_notifies_that_roles_users(): void
    {
        $sender = User::factory()->create();
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $manager = User::factory()->create();
        $manager->assignRole('General Manager');

        $announcement = $this->service()->send($sender, [
            'title' => 'For executives',
            'message' => 'Hello team',
            'audience' => AnnouncementAudience::Role->value,
            'audience_role' => 'Sales Executive',
            'audience_territory_id' => null,
            'audience_user_id' => null,
        ]);

        $this->assertSame(1, $announcement->recipient_count);
        $this->assertCount(1, $executive->fresh()->notifications);
        $this->assertCount(0, $manager->fresh()->notifications);
    }

    public function test_sending_to_a_territory_only_notifies_users_in_that_territory(): void
    {
        $sender = User::factory()->create();
        $territory = Territory::factory()->create();
        $inTerritory = User::factory()->inTerritory($territory)->create();
        $outsideTerritory = User::factory()->create();

        $announcement = $this->service()->send($sender, [
            'title' => 'Territory update',
            'message' => 'Hello territory',
            'audience' => AnnouncementAudience::Territory->value,
            'audience_role' => null,
            'audience_territory_id' => $territory->id,
            'audience_user_id' => null,
        ]);

        $this->assertSame(1, $announcement->recipient_count);
        $this->assertCount(1, $inTerritory->fresh()->notifications);
        $this->assertCount(0, $outsideTerritory->fresh()->notifications);
    }

    public function test_sending_to_a_single_user_only_notifies_that_user(): void
    {
        $sender = User::factory()->create();
        $target = User::factory()->create();
        $other = User::factory()->create();

        $announcement = $this->service()->send($sender, [
            'title' => 'Personal note',
            'message' => 'Hello you',
            'audience' => AnnouncementAudience::User->value,
            'audience_role' => null,
            'audience_territory_id' => null,
            'audience_user_id' => $target->id,
        ]);

        $this->assertSame(1, $announcement->recipient_count);
        $this->assertCount(1, $target->fresh()->notifications);
        $this->assertCount(0, $other->fresh()->notifications);
    }
}
