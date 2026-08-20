<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Notifications\BirthdayNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): NotificationService
    {
        return app(NotificationService::class);
    }

    public function test_unread_count_only_counts_unread_notifications(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        Notification::send($user, new BirthdayNotification($user));
        $user->notifications->first()->markAsRead();

        $this->assertSame(1, $this->service()->unreadCount($user));
    }

    public function test_mark_as_read_marks_only_the_given_notification(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        Notification::send($user, new BirthdayNotification($user));
        $target = $user->notifications->first();

        $this->service()->markAsRead($user, (string) $target->id);

        $this->assertNotNull($target->fresh()->read_at);
        $this->assertSame(1, $this->service()->unreadCount($user));
    }

    public function test_mark_all_as_read_clears_every_unread_notification(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        Notification::send($user, new BirthdayNotification($user));

        $count = $this->service()->markAllAsRead($user);

        $this->assertSame(2, $count);
        $this->assertSame(0, $this->service()->unreadCount($user));
    }

    public function test_delete_removes_only_the_given_notification(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        Notification::send($user, new BirthdayNotification($user));
        $target = $user->notifications->first();

        $this->service()->delete($user, (string) $target->id);

        $this->assertCount(1, $user->fresh()->notifications);
    }

    public function test_paginate_filters_by_read_status(): void
    {
        $user = User::factory()->create();
        Notification::send($user, new BirthdayNotification($user));
        Notification::send($user, new BirthdayNotification($user));
        $user->notifications->first()->markAsRead();

        $unread = $this->service()->paginate($user, ['status' => 'unread']);
        $read = $this->service()->paginate($user, ['status' => 'read']);

        $this->assertSame(1, $unread->total());
        $this->assertSame(1, $read->total());
    }
}
