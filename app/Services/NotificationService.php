<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Every notification is inherently scoped to its own notifiable (a user only
 * ever sees their own inbox), so — unlike the other modules — there's no
 * Policy class here; the query itself is the authorization boundary.
 */
class NotificationService
{
    /**
     * Short TTL, not "forever": new notifications arrive from many scattered
     * call sites (5 system-check Actions + manual admin sends) that don't
     * invalidate this cache, so a bit of staleness on arrival is accepted —
     * the same trade-off already made for Live GPS polling. The mark-read
     * paths below invalidate immediately instead, since a stale badge right
     * after the user acts on it would actually look broken.
     */
    private const BELL_TTL_SECONDS = 60;

    /**
     * @param  array{status?: string}  $filters
     */
    public function paginate(User $user, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $user->notifications()
            ->when(($filters['status'] ?? null) === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when(($filters['status'] ?? null) === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->paginate($perPage)
            ->withQueryString();
    }

    public function unreadCount(User $user): int
    {
        return Cache::remember(
            $this->bellCacheKey('unread_count', $user),
            self::BELL_TTL_SECONDS,
            fn () => $user->unreadNotifications()->count(),
        );
    }

    /**
     * @return DatabaseNotification[]|Collection
     */
    public function recent(User $user, int $limit = 6)
    {
        return Cache::remember(
            $this->bellCacheKey("recent.{$limit}", $user),
            self::BELL_TTL_SECONDS,
            fn () => $user->notifications()->latest()->limit($limit)->get(),
        );
    }

    public function markAsRead(User $user, string $id): DatabaseNotification
    {
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        $this->forgetBellCache($user);

        return $notification;
    }

    public function markAllAsRead(User $user): int
    {
        $unread = $user->unreadNotifications;
        $count = $unread->count();
        $unread->markAsRead();

        $this->forgetBellCache($user);

        return $count;
    }

    public function delete(User $user, string $id): void
    {
        $user->notifications()->findOrFail($id)->delete();

        $this->forgetBellCache($user);
    }

    private function bellCacheKey(string $suffix, User $user): string
    {
        return "notifications.{$suffix}.{$user->id}";
    }

    /**
     * Bounded to the two keys the bell dropdown actually reads — new
     * notifications arriving from elsewhere (5 scheduled Actions, manual
     * admin sends) rely on BELL_TTL_SECONDS to eventually surface instead.
     */
    private function forgetBellCache(User $user): void
    {
        Cache::forget($this->bellCacheKey('unread_count', $user));
        Cache::forget($this->bellCacheKey('recent.6', $user));
    }
}
