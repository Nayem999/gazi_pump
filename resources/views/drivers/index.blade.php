@extends('layouts.admin')

@section('title', 'Drivers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Drivers</li>
@endsection

@section('content')
    <x-filter-bar :action="route('drivers.index')">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or license number..." value="{{ $filters['search'] ?? '' }}">
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

    <form id="bulkForm" method="POST" action="{{ route('drivers.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected drivers?">
        @csrf
    </form>

    <div>
        <x-data-table
            title="Drivers"
            :create-url="auth()->user()->can('create', \App\Models\Driver::class) ? route('drivers.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Driver::class) ? route('drivers.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Driver::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Driver::class) ? route('drivers.print', request()->query()) : null"
            :paginator="$drivers"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Name</th>
                    <th>License Number</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($drivers as $driver)
                <tr>
                    <td>
                        @if (! $driver->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $driver->id }}" class="form-check-input row-checkbox" form="bulkForm">
                        @endif
                    </td>
                    <td>{{ $driver->name }}</td>
                    <td>{{ $driver->license_number }}</td>
                    <td>{{ $driver->phone ?? '—' }}</td>
                    <td>
                        @if ($driver->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $driver->status ? 'success' : 'secondary' }}">{{ $driver->status ? 'Active' : 'Inactive' }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($driver->trashed())
                                @can('restore', $driver)
                                    <form method="POST" action="{{ route('drivers.restore', $driver->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $driver)
                                    <form method="POST" action="{{ route('drivers.force-destroy', $driver->id) }}" data-confirm data-confirm-title="Permanently delete this driver?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $driver)
                                    <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $driver)
                                    <form method="POST" action="{{ route('drivers.destroy', $driver) }}" data-confirm data-confirm-title="Move this driver to trash?">
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
                    <td colspan="6" class="text-center text-muted py-4">No drivers found.</td>
                </tr>
            @endforelse
        </x-data-table>

        @can('drivers.delete')
            <div class="mt-2">
                <button type="submit" form="bulkForm" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </div>

    @can('import', \App\Models\Driver::class)
        <x-modal id="importModal" title="Import Drivers">
            <form id="importForm" method="POST" action="{{ route('drivers.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: name, license_number, phone.</div>
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
