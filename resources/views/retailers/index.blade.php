@extends('layouts.admin')

@section('title', 'Retailers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Retailers</li>
@endsection

@section('content')
    <x-filter-bar :action="route('retailers.index')">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or phone..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Dealer</label>
            <select name="dealer_id" class="form-select">
                <option value="">All</option>
                @foreach ($dealers as $dealer)
                    <option value="{{ $dealer->id }}" @selected((string) ($filters['dealer_id'] ?? '') === (string) $dealer->id)>{{ $dealer->name }}</option>
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

    <form id="bulkForm" method="POST" action="{{ route('retailers.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected retailers?">
        @csrf
        <x-data-table
            title="All Retailers"
            :create-url="auth()->user()->can('create', \App\Models\Retailer::class) ? route('retailers.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Retailer::class) ? route('retailers.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Retailer::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Retailer::class) ? route('retailers.print', request()->query()) : null"
            :paginator="$retailers"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th></th>
                    <th>Name</th>
                    <th>Dealer</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($retailers as $retailer)
                <tr>
                    <td>
                        @if (! $retailer->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $retailer->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        @if ($retailer->image)
                            <img src="{{ $retailer->imageUrl() }}" style="width:36px;height:36px;object-fit:cover" class="rounded">
                        @else
                            <i class="ti ti-building-cottage text-secondary fs-5"></i>
                        @endif
                    </td>
                    <td>{{ $retailer->name }}</td>
                    <td>{{ $retailer->dealer?->name }}</td>
                    <td>{{ $retailer->phone }}</td>
                    <td>
                        @if ($retailer->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $retailer->status ? 'success' : 'secondary' }}">
                                {{ $retailer->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($retailer->trashed())
                                @can('restore', $retailer)
                                    <form method="POST" action="{{ route('retailers.restore', $retailer->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $retailer)
                                    <form method="POST" action="{{ route('retailers.force-destroy', $retailer->id) }}" data-confirm data-confirm-title="Permanently delete this retailer?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $retailer)
                                    <a href="{{ route('retailers.edit', $retailer) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $retailer)
                                    <form method="POST" action="{{ route('retailers.destroy', $retailer) }}" data-confirm data-confirm-title="Move this retailer to trash?">
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
                    <td colspan="7" class="text-center text-muted py-4">No retailers found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($retailers as $retailer)
                    <div class="col">
                        <x-item-card
                            icon="ti-building-cottage"
                            icon-color="warning"
                            :image="$retailer->image ? $retailer->imageUrl() : null"
                            :title="$retailer->name"
                            :subtitle="$retailer->phone"
                            :status-label="$retailer->trashed() ? 'Trashed' : ($retailer->status ? 'Active' : 'Inactive')"
                            :status-color="$retailer->trashed() ? 'danger' : ($retailer->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>Dealer: {{ $retailer->dealer?->name }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $retailer->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $retailer->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($retailer->trashed())
                                    @can('restore', $retailer)
                                        <form method="POST" action="{{ route('retailers.restore', $retailer->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $retailer)
                                        <form method="POST" action="{{ route('retailers.force-destroy', $retailer->id) }}" data-confirm data-confirm-title="Permanently delete this retailer?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $retailer)
                                        <a href="{{ route('retailers.edit', $retailer) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $retailer)
                                        <form method="POST" action="{{ route('retailers.destroy', $retailer) }}" data-confirm data-confirm-title="Move this retailer to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No retailers found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('retailers.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Retailer::class)
        <x-modal id="importModal" title="Import Retailers">
            <form id="importForm" method="POST" action="{{ route('retailers.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: dealer_code, name, phone, email, shipping_address.</div>
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
