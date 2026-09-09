@extends('layouts.admin')

@section('title', 'Vehicles')

@section('breadcrumb')
    <li class="breadcrumb-item active">Vehicles</li>
@endsection

@section('content')
    <x-filter-bar :action="route('vehicles.index')">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Registration number or type..." value="{{ $filters['search'] ?? '' }}">
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

    <form id="bulkForm" method="POST" action="{{ route('vehicles.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected vehicles?">
        @csrf
    </form>

    <div>
        <x-data-table
            title="Vehicles"
            :create-url="auth()->user()->can('create', \App\Models\Vehicle::class) ? route('vehicles.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Vehicle::class) ? route('vehicles.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Vehicle::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Vehicle::class) ? route('vehicles.print', request()->query()) : null"
            :paginator="$vehicles"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Registration Number</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($vehicles as $vehicle)
                <tr>
                    <td>
                        @if (! $vehicle->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $vehicle->id }}" class="form-check-input row-checkbox" form="bulkForm">
                        @endif
                    </td>
                    <td>{{ $vehicle->registration_number }}</td>
                    <td>{{ $vehicle->type ?? '—' }}</td>
                    <td>{{ $vehicle->capacity ?? '—' }}</td>
                    <td>
                        @if ($vehicle->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $vehicle->status ? 'success' : 'secondary' }}">{{ $vehicle->status ? 'Active' : 'Inactive' }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($vehicle->trashed())
                                @can('restore', $vehicle)
                                    <form method="POST" action="{{ route('vehicles.restore', $vehicle->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $vehicle)
                                    <form method="POST" action="{{ route('vehicles.force-destroy', $vehicle->id) }}" data-confirm data-confirm-title="Permanently delete this vehicle?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $vehicle)
                                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $vehicle)
                                    <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" data-confirm data-confirm-title="Move this vehicle to trash?">
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
                    <td colspan="6" class="text-center text-muted py-4">No vehicles found.</td>
                </tr>
            @endforelse
        </x-data-table>

        @can('vehicles.delete')
            <div class="mt-2">
                <button type="submit" form="bulkForm" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </div>

    @can('import', \App\Models\Vehicle::class)
        <x-modal id="importModal" title="Import Vehicles">
            <form id="importForm" method="POST" action="{{ route('vehicles.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: registration_number, type, capacity.</div>
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
