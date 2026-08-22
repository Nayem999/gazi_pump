@extends('layouts.admin')

@section('title', 'Divisions')

@section('breadcrumb')
    <li class="breadcrumb-item active">Divisions</li>
@endsection

@section('content')
    <x-filter-bar :action="route('divisions.index')">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('divisions.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected divisions?">
        @csrf
        <x-data-table
            title="All Divisions"
            :create-url="auth()->user()->can('create', \App\Models\Division::class) ? route('divisions.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Division::class) ? route('divisions.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Division::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Division::class) ? route('divisions.print', request()->query()) : null"
            :paginator="$divisions"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Name</th>
                    <th>Name (Bangla)</th>
                    <th>Districts</th>
                    <th>Territories</th>
                    <th>Dealers</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($divisions as $division)
                <tr>
                    <td>
                        @if (! $division->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $division->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $division->name }}</td>
                    <td>{{ $division->name_bn }}</td>
                    <td><span class="badge text-bg-secondary">{{ $division->districts_count }}</span></td>
                    <td><span class="badge text-bg-secondary">{{ $division->territories_count }}</span></td>
                    <td><span class="badge text-bg-secondary">{{ $division->dealers_count }}</span></td>
                    <td>
                        @if ($division->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $division->status ? 'success' : 'secondary' }}">
                                {{ $division->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($division->trashed())
                                @can('restore', $division)
                                    <form method="POST" action="{{ route('divisions.restore', $division->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $division)
                                    <form method="POST" action="{{ route('divisions.force-destroy', $division->id) }}" data-confirm data-confirm-title="Permanently delete this division?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $division)
                                    <a href="{{ route('divisions.edit', $division) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $division)
                                    <form method="POST" action="{{ route('divisions.destroy', $division) }}" data-confirm data-confirm-title="Move this division to trash?">
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
                    <td colspan="8" class="text-center text-muted py-4">No divisions found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($divisions as $division)
                    <div class="col">
                        <x-item-card
                            icon="ti-map-2"
                            icon-color="warning"
                            :title="$division->name"
                            :subtitle="$division->name_bn"
                            :status-label="$division->trashed() ? 'Trashed' : ($division->status ? 'Active' : 'Inactive')"
                            :status-color="$division->trashed() ? 'danger' : ($division->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>Districts: {{ $division->districts_count }}</div>
                                <div>Territories: {{ $division->territories_count }}</div>
                                <div>Dealers: {{ $division->dealers_count }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $division->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $division->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($division->trashed())
                                    @can('restore', $division)
                                        <form method="POST" action="{{ route('divisions.restore', $division->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $division)
                                        <form method="POST" action="{{ route('divisions.force-destroy', $division->id) }}" data-confirm data-confirm-title="Permanently delete this division?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $division)
                                        <a href="{{ route('divisions.edit', $division) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $division)
                                        <form method="POST" action="{{ route('divisions.destroy', $division) }}" data-confirm data-confirm-title="Move this division to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No divisions found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('divisions.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Division::class)
        <x-modal id="importModal" title="Import Divisions">
            <form id="importForm" method="POST" action="{{ route('divisions.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: name, name_bn.</div>
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
