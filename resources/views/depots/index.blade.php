@extends('layouts.admin')

@section('title', 'Depots')

@section('breadcrumb')
    <li class="breadcrumb-item active">Depots</li>
@endsection

@section('content')
    <x-filter-bar :action="route('depots.index')">
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
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('depots.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected depots?">
        @csrf
    </form>

    <div>
        <x-data-table
            title="Depots"
            :create-url="auth()->user()->can('create', \App\Models\Depot::class) ? route('depots.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Depot::class) ? route('depots.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Depot::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Depot::class) ? route('depots.print', request()->query()) : null"
            :paginator="$depots"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Territory</th>
                    <th>Tally GUID</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($depots as $depot)
                <tr>
                    <td>
                        @if (! $depot->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $depot->id }}" class="form-check-input row-checkbox" form="bulkForm">
                        @endif
                    </td>
                    <td>{{ $depot->code }}</td>
                    <td>{{ $depot->name }}</td>
                    <td>{{ $depot->territory?->name ?? '—' }}</td>
                    <td class="font-monospace small">{{ $depot->tally_guid ?? '—' }}</td>
                    <td>
                        @if ($depot->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $depot->status ? 'success' : 'secondary' }}">{{ $depot->status ? 'Active' : 'Inactive' }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($depot->trashed())
                                @can('restore', $depot)
                                    <form method="POST" action="{{ route('depots.restore', $depot->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $depot)
                                    <form method="POST" action="{{ route('depots.force-destroy', $depot->id) }}" data-confirm data-confirm-title="Permanently delete this depot?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                <a href="{{ route('depot-stock.index', ['depot_id' => $depot->id]) }}" class="btn btn-outline-secondary" title="View Stock"><i class="ti ti-box"></i></a>
                                @can('update', $depot)
                                    <a href="{{ route('depots.edit', $depot) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $depot)
                                    <form method="POST" action="{{ route('depots.destroy', $depot) }}" data-confirm data-confirm-title="Move this depot to trash?">
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
                    <td colspan="7" class="text-center text-muted py-4">No depots found.</td>
                </tr>
            @endforelse
        </x-data-table>

        @can('depots.delete')
            <div class="mt-2">
                <button type="submit" form="bulkForm" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </div>

    @can('import', \App\Models\Depot::class)
        <x-modal id="importModal" title="Import Depots">
            <form id="importForm" method="POST" action="{{ route('depots.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: name, code, address.</div>
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
