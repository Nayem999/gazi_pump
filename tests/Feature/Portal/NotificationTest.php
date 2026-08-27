<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\Announcement;
use App\Models\CustomerAccount;
use App\Notifications\AnnouncementNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function notify(CustomerAccount $account): void
    {
        $announcement = Announcement::factory()->create(['title' => 'Hello Dealer']);
        Notification::send($account, new AnnouncementNotification($announcement));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('portal.notifications.index'))->assertRedirect(route('portal.login'));
    }

    public function test_an_account_can_view_its_own_notifications(): void
    {
        $account = CustomerAccount::factory()->create();
        $this->notify($account);

        $this->actingAs($account, 'customer')
            ->get(route('portal.notifications.index'))
            ->assertOk()
            ->assertSee('Hello Dealer');
    }

    public function test_marking_a_notification_as_read_works(): void
    {
        $account = CustomerAccount::factory()->create();
        $this->notify($account);
        $notification = $account->notifications->first();

        $this->actingAs($account, 'customer')->post(route('portal.notifications.read', $notification->id))->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_works(): void
    {
        $account = CustomerAccount::factory()->create();
        $this->notify($account);
        $this->notify($account);

        $this->actingAs($account, 'customer')->post(route('portal.notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $account->fresh()->unreadNotifications()->count());
    }

    public function test_deleting_a_notification_works(): void
    {
        $account = CustomerAccount::factory()->create();
        $this->notify($account);
        $notification = $account->notifications->first();

        $this->actingAs($account, 'customer')->delete(route('portal.notifications.destroy', $notification->id))->assertRedirect();

        $this->assertCount(0, $account->fresh()->notifications);
    }

    public function test_an_account_cannot_mark_another_accounts_notification_as_read(): void
    {
        $owner = CustomerAccount::factory()->create();
        $intruder = CustomerAccount::factory()->create();
        $this->notify($owner);
        $notification = $owner->notifications->first();

        $this->actingAs($intruder, 'customer')->post(route('portal.notifications.read', $notification->id))->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}
