@extends('layouts.admin')

@section('title', 'Achievement')

@section('breadcrumb')
    <li class="breadcrumb-item active">Achievement</li>
@endsection

@section('content')
    <x-filter-bar :action="route('achievements.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Executive name or ID..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Executive</label>
            <select name="user_id" class="form-select">
                <option value="">All</option>
                @foreach ($executives as $executive)
                    <option value="{{ $executive->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $executive->id)>{{ $executive->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Approval</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach (\App\Enums\ApprovalStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('achievements.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected achievements?">
        @csrf
        <x-data-table
            title="Achievement"
            :create-url="auth()->user()->can('create', \App\Models\AchievementEntry::class) ? route('achievements.create') : null"
            create-label="Record Achievement"
            :export-url="auth()->user()->can('export', \App\Models\AchievementEntry::class) ? route('achievements.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\AchievementEntry::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\AchievementEntry::class) ? route('achievements.print', request()->query()) : null"
            :paginator="$achievementEntries"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Executive</th>
                    <th>Date</th>
                    <th>Order Achieved</th>
                    <th>Collection Achieved</th>
                    <th>Quantity Achieved</th>
                    <th>Approval</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($achievementEntries as $entry)
                <tr>
                    <td>
                        @if (! $entry->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $entry->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        {{ $entry->user?->name }}
                        <div class="text-muted small">{{ $entry->user?->employee_id }}</div>
                    </td>
                    <td>
                        {{ $entry->entryDateLabel() }}
                        @if ($entry->isProductWise())
                            <div><span class="badge text-bg-info">Product-wise</span></div>
                        @endif
                    </td>
                    <td>{{ number_format((float) $entry->order_value_achieved, 2) }}</td>
                    <td>{{ number_format((float) $entry->collection_achieved, 2) }}</td>
                    <td>{{ $entry->quantity_achieved }}</td>
                    <td><span class="badge text-bg-{{ $entry->status->badgeColor() }}">{{ $entry->status->label() }}</span></td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($entry->trashed())
                                @can('restore', $entry)
                                    <form method="POST" action="{{ route('achievements.restore', $entry->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $entry)
                                    <form method="POST" action="{{ route('achievements.force-destroy', $entry->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('view', $entry)
                                    <a href="{{ route('achievements.show', $entry) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                @endcan
                                @can('update', $entry)
                                    <a href="{{ route('achievements.edit', $entry) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @if ($entry->status === \App\Enums\ApprovalStatus::Pending)
                                    @can('approve', $entry)
                                        <form method="POST" action="{{ route('achievements.approve', $entry) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-success" title="Approve"><i class="ti ti-check"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('achievements.reject', $entry) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-danger" title="Reject"><i class="ti ti-x"></i></button>
                                        </form>
                                    @endcan
                                @endif
                                @can('delete', $entry)
                                    <form method="POST" action="{{ route('achievements.destroy', $entry) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <td colspan="8" class="text-center text-muted py-4">No achievement entries found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($achievementEntries as $entry)
                    <div class="col">
                        <x-item-card
                            icon="ti-trophy"
                            :icon-color="$entry->status->badgeColor()"
                            :title="$entry->user?->name"
                            :subtitle="$entry->entryDateLabel().($entry->isProductWise() ? ' · Product-wise' : '')"
                            :status-label="$entry->trashed() ? 'Trashed' : $entry->status->label()"
                            :status-color="$entry->trashed() ? 'danger' : $entry->status->badgeColor()"
                        >
                            <x-slot:meta>
                                <div>Order Achieved: {{ number_format((float) $entry->order_value_achieved, 2) }}</div>
                                <div>Collection Achieved: {{ number_format((float) $entry->collection_achieved, 2) }}</div>
                                <div>Quantity Achieved: {{ $entry->quantity_achieved }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $entry->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $entry->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($entry->trashed())
                                    @can('restore', $entry)
                                        <form method="POST" action="{{ route('achievements.restore', $entry->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $entry)
                                        <form method="POST" action="{{ route('achievements.force-destroy', $entry->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('view', $entry)
                                        <a href="{{ route('achievements.show', $entry) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                    @endcan
                                    @can('update', $entry)
                                        <a href="{{ route('achievements.edit', $entry) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @if ($entry->status === \App\Enums\ApprovalStatus::Pending)
                                        @can('approve', $entry)
                                            <form method="POST" action="{{ route('achievements.approve', $entry) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-success" title="Approve"><i class="ti ti-check"></i></button>
                                            </form>
                                            <form method="POST" action="{{ route('achievements.reject', $entry) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-danger" title="Reject"><i class="ti ti-x"></i></button>
                                            </form>
                                        @endcan
                                    @endif
                                    @can('delete', $entry)
                                        <form method="POST" action="{{ route('achievements.destroy', $entry) }}" data-confirm data-confirm-title="Move this record to trash?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="ti ti-trash"></i></button>
                                        </form>
                                    @endcan
                                @endif
                            </x-slot:actions>
                        </x-item-card>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">No achievement entries found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('achievements.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\AchievementEntry::class)
        <x-modal id="importModal" title="Import Achievements">
            <form id="importForm" method="POST" action="{{ route('achievements.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: employee_id, entry_date, order_value_achieved, collection_achieved, quantity_achieved, notes.</div>
                </div>
            </form>
            <x-slot:footer>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="importForm" class="btn btn-primary">Import</button>
            </x-slot:footer>
        </x-modal>
    @endcan
@endsection

@push('scripts')
    <script>
        document.getElementById('selectAll')?.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach((cb) => { cb.checked = this.checked; });
        });
    </script>
@endpush
