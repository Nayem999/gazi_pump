<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use App\Notifications\BirthdayNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return $user->createToken('phpunit')->plainTextToken;
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/notifications')->assertStatus(401);
    }

    public function test_authenticated_user_can_list_their_own_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        Notification::send($otherUser, new BirthdayNotification($otherUser));

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_unread_count_endpoint_reports_the_correct_number(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        Notification::send($user, new BirthdayNotification($user));
        $user->notifications->first()->markAsRead();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1);
    }

    public function test_mark_read_endpoint_marks_a_single_notification(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        $notification = $user->notifications->first();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->postJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_read_endpoint_clears_every_unread_notification(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        Notification::send($user, new BirthdayNotification($user));

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->postJson('/api/v1/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('data.marked_read', 2);
    }
}
