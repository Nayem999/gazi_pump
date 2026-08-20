@extends('layouts.admin')

@section('title', 'Customers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Customers</li>
@endsection

@section('content')
    <x-filter-bar :action="route('customers.index')">
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
        <div class="col-md-2">
            <label class="form-label">Trashed</label>
            <select name="trashed" class="form-select">
                <option value="">Without Trashed</option>
                <option value="with" @selected(($filters['trashed'] ?? '') === 'with')>With Trashed</option>
                <option value="only" @selected(($filters['trashed'] ?? '') === 'only')>Only Trashed</option>
            </select>
        </div>
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('customers.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected customers?">
        @csrf
        <x-data-table
            title="All Customers"
            :create-url="auth()->user()->can('create', \App\Models\Customer::class) ? route('customers.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Customer::class) ? route('customers.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Customer::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Customer::class) ? route('customers.print', request()->query()) : null"
            :paginator="$customers"
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

            @forelse ($customers as $customer)
                <tr>
                    <td>
                        @if (! $customer->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $customer->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $customer->customer_code }}</td>
                    <td>{{ $customer->name }}</td>
                    <td><span class="badge text-bg-{{ $customer->type->badgeColor() }}">{{ $customer->type->label() }}</span></td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->territory?->name ?? '—' }}</td>
                    <td>
                        @if ($customer->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $customer->status ? 'success' : 'secondary' }}">
                                {{ $customer->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($customer->trashed())
                                @can('restore', $customer)
                                    <form method="POST" action="{{ route('customers.restore', $customer->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $customer)
                                    <form method="POST" action="{{ route('customers.force-destroy', $customer->id) }}" data-confirm data-confirm-title="Permanently delete this customer?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('view', $customer)
                                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                @endcan
                                @can('update', $customer)
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $customer)
                                    <form method="POST" action="{{ route('customers.destroy', $customer) }}" data-confirm data-confirm-title="Move this customer to trash?">
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
                    <td colspan="8" class="text-center text-muted py-4">No customers found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($customers as $customer)
                    <div class="col">
                        <x-item-card
                            icon="ti-building-store"
                            icon-color="info"
                            :title="$customer->name"
                            :subtitle="$customer->customer_code"
                            :status-label="$customer->trashed() ? 'Trashed' : ($customer->status ? 'Active' : 'Inactive')"
                            :status-color="$customer->trashed() ? 'danger' : ($customer->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div><span class="badge text-bg-{{ $customer->type->badgeColor() }}">{{ $customer->type->label() }}</span></div>
                                <div>Phone: {{ $customer->phone }}</div>
                                <div>Territory: {{ $customer->territory?->name ?? '—' }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $customer->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $customer->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($customer->trashed())
                                    @can('restore', $customer)
                                        <form method="POST" action="{{ route('customers.restore', $customer->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $customer)
                                        <form method="POST" action="{{ route('customers.force-destroy', $customer->id) }}" data-confirm data-confirm-title="Permanently delete this customer?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('view', $customer)
                                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                    @endcan
                                    @can('update', $customer)
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $customer)
                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" data-confirm data-confirm-title="Move this customer to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No customers found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('customers.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Customer::class)
        <x-modal id="importModal" title="Import Customers">
            <form id="importForm" method="POST" action="{{ route('customers.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: customer_code, name, type (dealer/retailer/distributor), phone, email, address, territory_code.</div>
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
