@extends('layouts.admin')

@section('title', 'Thanas')

@section('breadcrumb')
    <li class="breadcrumb-item active">Thanas</li>
@endsection

@section('content')
    <x-filter-bar :action="route('thanas.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Division</label>
            <select name="division_id" id="division_id" class="form-select">
                <option value="">All</option>
                @foreach ($divisions as $divisionOption)
                    <option value="{{ $divisionOption->id }}" @selected((string) ($filters['division_id'] ?? '') === (string) $divisionOption->id)>
                        {{ $divisionOption->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">District</label>
            <select name="district_id" id="district_id" class="form-select" data-initial-value="{{ $filters['district_id'] ?? '' }}" @if (empty($filters['division_id'])) disabled @endif>
                <option value="">All</option>
            </select>
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('thanas.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected thanas?">
        @csrf
        <x-data-table
            title="All Thanas"
            :create-url="auth()->user()->can('create', \App\Models\Thana::class) ? route('thanas.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Thana::class) ? route('thanas.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Thana::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Thana::class) ? route('thanas.print', request()->query()) : null"
            :paginator="$thanas"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Name</th>
                    <th>Name (Bangla)</th>
                    <th>District</th>
                    <th>Division</th>
                    <th>Territories</th>
                    <th>Dealers</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($thanas as $thana)
                <tr>
                    <td>
                        @if (! $thana->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $thana->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $thana->name }}</td>
                    <td>{{ $thana->name_bn }}</td>
                    <td>{{ $thana->district?->name ?? '—' }}</td>
                    <td>{{ $thana->district?->division?->name ?? '—' }}</td>
                    <td><span class="badge text-bg-secondary">{{ $thana->territories_count }}</span></td>
                    <td><span class="badge text-bg-secondary">{{ $thana->dealers_count }}</span></td>
                    <td>
                        @if ($thana->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $thana->status ? 'success' : 'secondary' }}">
                                {{ $thana->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($thana->trashed())
                                @can('restore', $thana)
                                    <form method="POST" action="{{ route('thanas.restore', $thana->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $thana)
                                    <form method="POST" action="{{ route('thanas.force-destroy', $thana->id) }}" data-confirm data-confirm-title="Permanently delete this thana?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $thana)
                                    <a href="{{ route('thanas.edit', $thana) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $thana)
                                    <form method="POST" action="{{ route('thanas.destroy', $thana) }}" data-confirm data-confirm-title="Move this thana to trash?">
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
                    <td colspan="9" class="text-center text-muted py-4">No thanas found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($thanas as $thana)
                    <div class="col">
                        <x-item-card
                            icon="ti-map-2"
                            icon-color="warning"
                            :title="$thana->name"
                            :subtitle="$thana->district?->name"
                            :status-label="$thana->trashed() ? 'Trashed' : ($thana->status ? 'Active' : 'Inactive')"
                            :status-color="$thana->trashed() ? 'danger' : ($thana->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>Division: {{ $thana->district?->division?->name ?? '—' }}</div>
                                <div>Territories: {{ $thana->territories_count }}</div>
                                <div>Dealers: {{ $thana->dealers_count }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $thana->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $thana->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($thana->trashed())
                                    @can('restore', $thana)
                                        <form method="POST" action="{{ route('thanas.restore', $thana->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $thana)
                                        <form method="POST" action="{{ route('thanas.force-destroy', $thana->id) }}" data-confirm data-confirm-title="Permanently delete this thana?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $thana)
                                        <a href="{{ route('thanas.edit', $thana) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $thana)
                                        <form method="POST" action="{{ route('thanas.destroy', $thana) }}" data-confirm data-confirm-title="Move this thana to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No thanas found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('thanas.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Thana::class)
        <x-modal id="importModal" title="Import Thanas">
            <form id="importForm" method="POST" action="{{ route('thanas.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: district (district name), name, name_bn.</div>
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
