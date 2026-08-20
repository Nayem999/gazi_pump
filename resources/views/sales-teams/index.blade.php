@extends('layouts.admin')

@section('title', 'Sales Teams')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales Teams</li>
@endsection

@section('content')
    <x-filter-bar :action="route('sales-teams.index')">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or code..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
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

    <form id="bulkForm" method="POST" action="{{ route('sales-teams.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected sales teams?">
        @csrf
        <x-data-table
            title="All Sales Teams"
            :create-url="auth()->user()->can('create', \App\Models\SalesTeam::class) ? route('sales-teams.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\SalesTeam::class) ? route('sales-teams.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\SalesTeam::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\SalesTeam::class) ? route('sales-teams.print', request()->query()) : null"
            :paginator="$salesTeams"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Members</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($salesTeams as $salesTeam)
                <tr>
                    <td>
                        @if (! $salesTeam->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $salesTeam->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $salesTeam->code }}</td>
                    <td>{{ $salesTeam->name }}</td>
                    <td><span class="badge text-bg-secondary">{{ $salesTeam->users_count }}</span></td>
                    <td>
                        @if ($salesTeam->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $salesTeam->status ? 'success' : 'secondary' }}">
                                {{ $salesTeam->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($salesTeam->trashed())
                                @can('restore', $salesTeam)
                                    <form method="POST" action="{{ route('sales-teams.restore', $salesTeam->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $salesTeam)
                                    <form method="POST" action="{{ route('sales-teams.force-destroy', $salesTeam->id) }}" data-confirm data-confirm-title="Permanently delete this sales team?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $salesTeam)
                                    <a href="{{ route('sales-teams.edit', $salesTeam) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $salesTeam)
                                    <form method="POST" action="{{ route('sales-teams.destroy', $salesTeam) }}" data-confirm data-confirm-title="Move this sales team to trash?">
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
                    <td colspan="6" class="text-center text-muted py-4">No sales teams found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($salesTeams as $salesTeam)
                    <div class="col">
                        <x-item-card
                            icon="ti-users-group"
                            icon-color="primary"
                            :title="$salesTeam->name"
                            :subtitle="$salesTeam->code"
                            :status-label="$salesTeam->trashed() ? 'Trashed' : ($salesTeam->status ? 'Active' : 'Inactive')"
                            :status-color="$salesTeam->trashed() ? 'danger' : ($salesTeam->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>Members: {{ $salesTeam->users_count }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $salesTeam->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $salesTeam->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($salesTeam->trashed())
                                    @can('restore', $salesTeam)
                                        <form method="POST" action="{{ route('sales-teams.restore', $salesTeam->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $salesTeam)
                                        <form method="POST" action="{{ route('sales-teams.force-destroy', $salesTeam->id) }}" data-confirm data-confirm-title="Permanently delete this sales team?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $salesTeam)
                                        <a href="{{ route('sales-teams.edit', $salesTeam) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $salesTeam)
                                        <form method="POST" action="{{ route('sales-teams.destroy', $salesTeam) }}" data-confirm data-confirm-title="Move this sales team to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No sales teams found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('sales-teams.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\SalesTeam::class)
        <x-modal id="importModal" title="Import Sales Teams">
            <form id="importForm" method="POST" action="{{ route('sales-teams.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: name, code, description.</div>
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
