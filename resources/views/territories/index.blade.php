@extends('layouts.admin')

@section('title', 'Territories')

@section('breadcrumb')
    <li class="breadcrumb-item active">Territories</li>
@endsection

@section('content')
    <x-filter-bar :action="route('territories.index')">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or code..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Trashed</label>
            <select name="trashed" class="form-select">
                <option value="">Without Trashed</option>
                <option value="with" @selected(($filters['trashed'] ?? '') === 'with')>With Trashed</option>
                <option value="only" @selected(($filters['trashed'] ?? '') === 'only')>Only Trashed</option>
            </select>
        </div>
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('territories.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected territories?">
        @csrf
        <x-data-table
            title="All Territories"
            :create-url="auth()->user()->can('create', \App\Models\Territory::class) ? route('territories.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Territory::class) ? route('territories.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Territory::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Territory::class) ? route('territories.print', request()->query()) : null"
            :paginator="$territories"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Manager</th>
                    <th>Executives</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($territories as $territory)
                <tr>
                    <td>
                        @if (! $territory->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $territory->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $territory->code }}</td>
                    <td>{{ $territory->name }}</td>
                    <td>{{ $territory->manager?->name ?? '—' }}</td>
                    <td><span class="badge text-bg-secondary">{{ $territory->users_count }}</span></td>
                    <td>
                        @if ($territory->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $territory->status ? 'success' : 'secondary' }}">
                                {{ $territory->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($territory->trashed())
                                @can('restore', $territory)
                                    <form method="POST" action="{{ route('territories.restore', $territory->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $territory)
                                    <form method="POST" action="{{ route('territories.force-destroy', $territory->id) }}" data-confirm data-confirm-title="Permanently delete this territory?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $territory)
                                    <a href="{{ route('territories.edit', $territory) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $territory)
                                    <form method="POST" action="{{ route('territories.destroy', $territory) }}" data-confirm data-confirm-title="Move this territory to trash?">
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
                    <td colspan="8" class="text-center text-muted py-4">No territories found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($territories as $territory)
                    <div class="col">
                        <x-item-card
                            icon="ti-map-pin-2"
                            icon-color="warning"
                            :title="$territory->name"
                            :subtitle="$territory->code"
                            :status-label="$territory->trashed() ? 'Trashed' : ($territory->status ? 'Active' : 'Inactive')"
                            :status-color="$territory->trashed() ? 'danger' : ($territory->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>Manager: {{ $territory->manager?->name ?? '—' }}</div>
                                <div>Executives: {{ $territory->users_count }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $territory->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $territory->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($territory->trashed())
                                    @can('restore', $territory)
                                        <form method="POST" action="{{ route('territories.restore', $territory->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $territory)
                                        <form method="POST" action="{{ route('territories.force-destroy', $territory->id) }}" data-confirm data-confirm-title="Permanently delete this territory?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $territory)
                                        <a href="{{ route('territories.edit', $territory) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $territory)
                                        <form method="POST" action="{{ route('territories.destroy', $territory) }}" data-confirm data-confirm-title="Move this territory to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No territories found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('territories.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Territory::class)
        <x-modal id="importModal" title="Import Territories">
            <form id="importForm" method="POST" action="{{ route('territories.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: name, code.</div>
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
