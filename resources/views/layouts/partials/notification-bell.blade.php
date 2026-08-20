<div class="dropdown">
    <button class="btn btn-light border position-relative" data-bs-toggle="dropdown" title="Notifications">
        <i class="ti ti-bell fs-5"></i>
        @if (($unreadNotificationsCount ?? 0) > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
            </span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end p-0" style="width:340px;max-width:90vw">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <h6 class="mb-0">Notifications</h6>
            @if (($unreadNotificationsCount ?? 0) > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
                </form>
            @endif
        </div>
        <div style="max-height:360px;overflow-y:auto">
            @forelse ($recentNotifications ?? [] as $notification)
                @php
                    $notificationType = \App\Enums\NotificationType::tryFrom($notification->data['type'] ?? '');
                    $notificationColor = $notificationType?->color() ?? 'primary';
                @endphp
                <div class="d-flex align-items-start gap-2 px-3 py-2 border-bottom {{ $notification->read_at ? '' : 'bg-body-tertiary' }}">
                    <div class="stat-card-icon bg-{{ $notificationColor }}-subtle text-{{ $notificationColor }} flex-shrink-0" style="width:36px;height:36px;font-size:0.9rem">
                        <i class="ti {{ $notificationType?->icon() ?? 'ti-bell' }}"></i>
                    </div>
                    <div class="flex-grow-1 small">
                        <div class="fw-semibold">{{ $notification->data['title'] ?? 'Notification' }}</div>
                        <div class="text-muted">{{ \Illuminate\Support\Str::limit($notification->data['message'] ?? '', 70) }}</div>
                        <div class="text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4 small">No notifications yet.</div>
            @endforelse
        </div>
        <a href="{{ route('notifications.index') }}" class="d-block text-center py-2 border-top small">View All</a>
    </div>
</div>
