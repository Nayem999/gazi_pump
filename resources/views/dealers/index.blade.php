@extends('layouts.admin')

@section('title', 'Dealers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dealers</li>
@endsection

@section('content')
    <x-filter-bar :action="route('dealers.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name, code, phone..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
                <option value="">All Types</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Territory</label>
            <select name="territory_id" class="form-select">
                <option value="">All Territories</option>
                @foreach ($territories as $territory)
                    <option value="{{ $territory->id }}" @selected((string) ($filters['territory_id'] ?? '') === (string) $territory->id)>{{ $territory->name }}</option>
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
        @include('partials.trashed-filter', ['filters' => $filters])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('dealers.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected dealers?">
        @csrf
        <x-data-table
            title="All Dealers"
            :create-url="auth()->user()->can('create', \App\Models\Dealer::class) ? route('dealers.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Dealer::class) ? route('dealers.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Dealer::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Dealer::class) ? route('dealers.print', request()->query()) : null"
            :paginator="$dealers"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Phone</th>
                    <th>Territory</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($dealers as $dealer)
                <tr>
                    <td>
                        @if (! $dealer->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $dealer->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $dealer->dealer_code }}</td>
                    <td>
                        @if ($dealer->trashed())
                            {{ $dealer->name }}
                        @else
                            <a href="{{ route('dealers.show', $dealer) }}">{{ $dealer->name }}</a>
                        @endif
                    </td>
                    <td><span class="badge text-bg-{{ $dealer->type->badgeColor() }}">{{ $dealer->type->label() }}</span></td>
                    <td><x-phone-actions :phone="$dealer->phone" /></td>
                    <td>{{ $dealer->territory?->name ?? '—' }}</td>
                    <td>
                        @if ($dealer->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $dealer->status ? 'success' : 'secondary' }}">
                                {{ $dealer->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($dealer->trashed())
                                @can('restore', $dealer)
                                    <form method="POST" action="{{ route('dealers.restore', $dealer->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $dealer)
                                    <form method="POST" action="{{ route('dealers.force-destroy', $dealer->id) }}" data-confirm data-confirm-title="Permanently delete this dealer?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('view', $dealer)
                                    <a href="{{ route('dealers.show', $dealer) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                @endcan
                                @can('update', $dealer)
                                    <a href="{{ route('dealers.edit', $dealer) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $dealer)
                                    <form method="POST" action="{{ route('dealers.destroy', $dealer) }}" data-confirm data-confirm-title="Move this dealer to trash?">
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
                    <td colspan="8" class="text-center text-muted py-4">No dealers found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($dealers as $dealer)
                    <div class="col">
                        <x-item-card
                            icon="ti-building-store"
                            icon-color="info"
                            :title="$dealer->name"
                            :title-url="$dealer->trashed() ? null : route('dealers.show', $dealer)"
                            :subtitle="$dealer->dealer_code"
                            :status-label="$dealer->trashed() ? 'Trashed' : ($dealer->status ? 'Active' : 'Inactive')"
                            :status-color="$dealer->trashed() ? 'danger' : ($dealer->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div><span class="badge text-bg-{{ $dealer->type->badgeColor() }}">{{ $dealer->type->label() }}</span></div>
                                <div>Phone: <x-phone-actions :phone="$dealer->phone" /></div>
                                <div>Territory: {{ $dealer->territory?->name ?? '—' }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $dealer->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $dealer->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($dealer->trashed())
                                    @can('restore', $dealer)
                                        <form method="POST" action="{{ route('dealers.restore', $dealer->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $dealer)
                                        <form method="POST" action="{{ route('dealers.force-destroy', $dealer->id) }}" data-confirm data-confirm-title="Permanently delete this dealer?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('view', $dealer)
                                        <a href="{{ route('dealers.show', $dealer) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                    @endcan
                                    @can('update', $dealer)
                                        <a href="{{ route('dealers.edit', $dealer) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $dealer)
                                        <form method="POST" action="{{ route('dealers.destroy', $dealer) }}" data-confirm data-confirm-title="Move this dealer to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No dealers found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('dealers.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Dealer::class)
        <x-modal id="importModal" title="Import Dealers">
            <form id="importForm" method="POST" action="{{ route('dealers.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: dealer_code, name, type (dealer/retailer/distributor), phone, email, address, territory_code.</div>
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
