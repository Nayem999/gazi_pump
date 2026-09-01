@extends('layouts.admin')

@section('title', 'Holidays')

@section('breadcrumb')
    <li class="breadcrumb-item active">Holidays</li>
@endsection

@section('content')
    <x-filter-bar :action="route('holidays.index')">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Holiday name..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control" placeholder="e.g. {{ now()->year }}" value="{{ $filters['year'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('holidays.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected holidays?">
        @csrf
        <x-data-table
            title="Government Holidays"
            :create-url="auth()->user()->can('create', \App\Models\Holiday::class) ? route('holidays.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Holiday::class) ? route('holidays.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Holiday::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Holiday::class) ? route('holidays.print', request()->query()) : null"
            :paginator="$holidays"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($holidays as $holiday)
                <tr>
                    <td>
                        @if (! $holiday->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $holiday->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $holiday->date->format('d M Y') }}</td>
                    <td>{{ $holiday->name }}</td>
                    <td>{{ $holiday->description }}</td>
                    <td>
                        @if ($holiday->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $holiday->status ? 'success' : 'secondary' }}">
                                {{ $holiday->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($holiday->trashed())
                                @can('restore', $holiday)
                                    <form method="POST" action="{{ route('holidays.restore', $holiday->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $holiday)
                                    <form method="POST" action="{{ route('holidays.force-destroy', $holiday->id) }}" data-confirm data-confirm-title="Permanently delete this holiday?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $holiday)
                                    <a href="{{ route('holidays.edit', $holiday) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $holiday)
                                    <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" data-confirm data-confirm-title="Move this holiday to trash?">
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
                    <td colspan="6" class="text-center text-muted py-4">No holidays found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($holidays as $holiday)
                    <div class="col">
                        <x-item-card
                            icon="ti-calendar-event"
                            icon-color="danger"
                            :title="$holiday->name"
                            :subtitle="$holiday->date->format('d M Y')"
                            :status-label="$holiday->trashed() ? 'Trashed' : ($holiday->status ? 'Active' : 'Inactive')"
                            :status-color="$holiday->trashed() ? 'danger' : ($holiday->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>{{ $holiday->description }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $holiday->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $holiday->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($holiday->trashed())
                                    @can('restore', $holiday)
                                        <form method="POST" action="{{ route('holidays.restore', $holiday->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $holiday)
                                        <form method="POST" action="{{ route('holidays.force-destroy', $holiday->id) }}" data-confirm data-confirm-title="Permanently delete this holiday?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $holiday)
                                        <a href="{{ route('holidays.edit', $holiday) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $holiday)
                                        <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" data-confirm data-confirm-title="Move this holiday to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No holidays found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('holidays.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Holiday::class)
        <x-modal id="importModal" title="Import Holidays">
            <form id="importForm" method="POST" action="{{ route('holidays.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: name, date (YYYY-MM-DD), description.</div>
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
