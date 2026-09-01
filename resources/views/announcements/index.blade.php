@extends('layouts.admin')

@section('title', 'Announcements')

@section('breadcrumb')
    <li class="breadcrumb-item active">Announcements</li>
@endsection

@section('content')
    <x-filter-bar :action="route('announcements.index')">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Title..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Audience</label>
            <select name="audience" class="form-select">
                <option value="">All</option>
                @foreach (\App\Enums\AnnouncementAudience::cases() as $audience)
                    <option value="{{ $audience->value }}" @selected(($filters['audience'] ?? '') === $audience->value)>{{ $audience->label() }}</option>
                @endforeach
            </select>
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('announcements.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected announcements?">
        @csrf
        <x-data-table
            title="Announcements"
            :create-url="auth()->user()->can('create', \App\Models\Announcement::class) ? route('announcements.create') : null"
            create-label="Send Announcement"
            :paginator="$announcements"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Title</th>
                    <th>Audience</th>
                    <th>Sent By</th>
                    <th>Recipients</th>
                    <th>Sent At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($announcements as $announcement)
                <tr>
                    <td>
                        @if (! $announcement->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $announcement->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        {{ $announcement->title }}
                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($announcement->message, 60) }}</div>
                    </td>
                    <td><span class="badge text-bg-info">{{ $announcement->audienceLabel() }}</span></td>
                    <td>{{ $announcement->sender?->name }}</td>
                    <td>{{ $announcement->recipient_count }}</td>
                    <td>{{ $announcement->created_at->format('d M Y, h:i A') }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($announcement->trashed())
                                @can('restore', $announcement)
                                    <form method="POST" action="{{ route('announcements.restore', $announcement->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $announcement)
                                    <form method="POST" action="{{ route('announcements.force-destroy', $announcement->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('delete', $announcement)
                                    <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" data-confirm data-confirm-title="Move this record to trash?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="ti ti-trash"></i></button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No announcements found.</td>
                </tr>
            @endforelse
        </x-data-table>

        @can('announcements.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('selectAll')?.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach((cb) => { cb.checked = this.checked; });
        });
    </script>
@endpush
