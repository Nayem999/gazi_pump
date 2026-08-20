<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Notifications\BirthdayNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_their_notifications_index(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));

        $this->actingAs($user)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Happy Birthday');
    }

    public function test_marking_a_notification_as_read_works(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        $notification = $user->notifications->first();

        $this->actingAs($user)->post(route('notifications.read', $notification->id))->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_works(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        Notification::send($user, new BirthdayNotification($user));

        $this->actingAs($user)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_deleting_a_notification_works(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        $notification = $user->notifications->first();

        $this->actingAs($user)->delete(route('notifications.destroy', $notification->id))->assertRedirect();

        $this->assertCount(0, $user->fresh()->notifications);
    }

    public function test_a_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        Notification::send($owner, new BirthdayNotification($owner));
        $notification = $owner->notifications->first();

        $this->actingAs($intruder)->post(route('notifications.read', $notification->id))->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}
