@extends('layouts.portal-account')

@section('title', 'My Notifications')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <h1 class="mb-0">My Notifications</h1>
        <form method="POST" action="{{ route('portal.notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="ti ti-checks me-1"></i>Mark All as Read</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <select name="status" class="form-select form-select-sm" style="width:auto" onchange="window.location = '{{ route('portal.notifications.index') }}?status=' + this.value">
                <option value="">All</option>
                <option value="unread" @selected(($filters['status'] ?? '') === 'unread')>Unread</option>
                <option value="read" @selected(($filters['status'] ?? '') === 'read')>Read</option>
            </select>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush">
                @forelse ($notifications as $notification)
                    @php
                        $notificationType = \App\Enums\NotificationType::tryFrom($notification->data['type'] ?? '');
                        $notificationColor = $notificationType?->color() ?? 'primary';
                    @endphp
                    <div class="list-group-item d-flex align-items-start gap-3 {{ $notification->read_at ? '' : 'bg-body-tertiary' }}">
                        <div class="stat-card-icon bg-{{ $notificationColor }}-subtle text-{{ $notificationColor }} flex-shrink-0" style="width:48px;height:48px;font-size:1.1rem">
                            <i class="ti {{ $notificationType?->icon() ?? 'ti-bell' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2">
                                <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                                @if (! $notification->read_at)
                                    <span class="badge text-bg-primary">New</span>
                                @endif
                            </div>
                            <div class="text-muted">{{ $notification->data['message'] ?? '' }}</div>
                            <div class="text-muted small mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="d-flex gap-1">
                            @unless ($notification->read_at)
                                <form method="POST" action="{{ route('portal.notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark as read"><i class="ti ti-check"></i></button>
                                </form>
                            @endunless
                            <form method="POST" action="{{ route('portal.notifications.destroy', $notification->id) }}" data-confirm data-confirm-title="Delete this notification?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="ti ti-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">You don't have any notifications yet.</div>
                @endforelse
            </div>

            @if ($notifications->hasPages())
                <div class="d-flex justify-content-end mt-3">
                    {{ $notifications->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
