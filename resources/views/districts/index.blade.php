@extends('layouts.admin')

@section('title', 'Districts')

@section('breadcrumb')
    <li class="breadcrumb-item active">Districts</li>
@endsection

@section('content')
    <x-filter-bar :action="route('districts.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Division</label>
            <select name="division_id" class="form-select">
                <option value="">All</option>
                @foreach ($divisions as $divisionOption)
                    <option value="{{ $divisionOption->id }}" @selected((string) ($filters['division_id'] ?? '') === (string) $divisionOption->id)>
                        {{ $divisionOption->name }}
                    </option>
                @endforeach
            </select>
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

    <form id="bulkForm" method="POST" action="{{ route('districts.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected districts?">
        @csrf
        <x-data-table
            title="All Districts"
            :create-url="auth()->user()->can('create', \App\Models\District::class) ? route('districts.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\District::class) ? route('districts.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\District::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\District::class) ? route('districts.print', request()->query()) : null"
            :paginator="$districts"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Name</th>
                    <th>Name (Bangla)</th>
                    <th>Division</th>
                    <th>Thanas</th>
                    <th>Territories</th>
                    <th>Dealers</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($districts as $district)
                <tr>
                    <td>
                        @if (! $district->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $district->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $district->name }}</td>
                    <td>{{ $district->name_bn }}</td>
                    <td>{{ $district->division?->name ?? '—' }}</td>
                    <td><span class="badge text-bg-secondary">{{ $district->thanas_count }}</span></td>
                    <td><span class="badge text-bg-secondary">{{ $district->territories_count }}</span></td>
                    <td><span class="badge text-bg-secondary">{{ $district->dealers_count }}</span></td>
                    <td>
                        @if ($district->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $district->status ? 'success' : 'secondary' }}">
                                {{ $district->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($district->trashed())
                                @can('restore', $district)
                                    <form method="POST" action="{{ route('districts.restore', $district->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $district)
                                    <form method="POST" action="{{ route('districts.force-destroy', $district->id) }}" data-confirm data-confirm-title="Permanently delete this district?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $district)
                                    <a href="{{ route('districts.edit', $district) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $district)
                                    <form method="POST" action="{{ route('districts.destroy', $district) }}" data-confirm data-confirm-title="Move this district to trash?">
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
                    <td colspan="9" class="text-center text-muted py-4">No districts found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($districts as $district)
                    <div class="col">
                        <x-item-card
                            icon="ti-map-2"
                            icon-color="warning"
                            :title="$district->name"
                            :subtitle="$district->division?->name"
                            :status-label="$district->trashed() ? 'Trashed' : ($district->status ? 'Active' : 'Inactive')"
                            :status-color="$district->trashed() ? 'danger' : ($district->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>Thanas: {{ $district->thanas_count }}</div>
                                <div>Territories: {{ $district->territories_count }}</div>
                                <div>Dealers: {{ $district->dealers_count }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $district->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $district->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($district->trashed())
                                    @can('restore', $district)
                                        <form method="POST" action="{{ route('districts.restore', $district->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $district)
                                        <form method="POST" action="{{ route('districts.force-destroy', $district->id) }}" data-confirm data-confirm-title="Permanently delete this district?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $district)
                                        <a href="{{ route('districts.edit', $district) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $district)
                                        <form method="POST" action="{{ route('districts.destroy', $district) }}" data-confirm data-confirm-title="Move this district to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No districts found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('districts.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\District::class)
        <x-modal id="importModal" title="Import Districts">
            <form id="importForm" method="POST" action="{{ route('districts.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: division (division name), name, name_bn.</div>
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
