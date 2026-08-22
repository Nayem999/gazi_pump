@extends('layouts.admin')

@section('title', 'Sales Entry')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales Entry</li>
@endsection

@section('content')
    <x-filter-bar :action="route('sales-entries.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Customer or product..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Executive</label>
            <select name="user_id" class="form-select">
                <option value="">All</option>
                @foreach ($executives as $executive)
                    <option value="{{ $executive->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $executive->id)>{{ $executive->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('sales-entries.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected sales entries?">
        @csrf
        <x-data-table
            title="Sales Entries"
            :create-url="auth()->user()->can('create', \App\Models\SalesEntry::class) ? route('sales-entries.create') : null"
            create-label="Record Sale"
            :export-url="auth()->user()->can('export', \App\Models\SalesEntry::class) ? route('sales-entries.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\SalesEntry::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\SalesEntry::class) ? route('sales-entries.print', request()->query()) : null"
            :paginator="$salesEntries"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Executive</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Sale Date</th>
                    <th>Total</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($salesEntries as $salesEntry)
                <tr>
                    <td>
                        @if (! $salesEntry->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $salesEntry->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        {{ $salesEntry->user?->name }}
                        <div class="text-muted small">{{ $salesEntry->user?->employee_id }}</div>
                    </td>
                    <td>
                        {{ $salesEntry->customer?->name }}
                        <div class="text-muted small">{{ $salesEntry->customer?->customer_code }}</div>
                    </td>
                    <td>
                        <span class="badge text-bg-secondary">{{ $salesEntry->items->count() }} item(s)</span>
                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($salesEntry->items->pluck('product.name')->filter()->implode(', '), 50) }}</div>
                    </td>
                    <td>{{ $salesEntry->sale_date->format('M d, Y') }}</td>
                    <td>{{ number_format((float) $salesEntry->total_amount, 2) }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($salesEntry->trashed())
                                @can('restore', $salesEntry)
                                    <form method="POST" action="{{ route('sales-entries.restore', $salesEntry->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $salesEntry)
                                    <form method="POST" action="{{ route('sales-entries.force-destroy', $salesEntry->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('view', $salesEntry)
                                    <a href="{{ route('sales-entries.show', $salesEntry) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                @endcan
                                @can('update', $salesEntry)
                                    <a href="{{ route('sales-entries.edit', $salesEntry) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $salesEntry)
                                    <form method="POST" action="{{ route('sales-entries.destroy', $salesEntry) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <td colspan="7" class="text-center text-muted py-4">No sales entries found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($salesEntries as $salesEntry)
                    <div class="col">
                        <x-item-card
                            icon="ti-receipt"
                            icon-color="primary"
                            :title="$salesEntry->customer?->name"
                            :subtitle="$salesEntry->items->count().' item(s)'"
                            :status-label="$salesEntry->trashed() ? 'Trashed' : null"
                            status-color="danger"
                        >
                            <x-slot:meta>
                                <div>Executive: {{ $salesEntry->user?->name }}</div>
                                <div>Date: {{ $salesEntry->sale_date->format('M d, Y') }}</div>
                                <div>{{ \Illuminate\Support\Str::limit($salesEntry->items->pluck('product.name')->filter()->implode(', '), 60) }}</div>
                                <div>Total: {{ number_format((float) $salesEntry->total_amount, 2) }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $salesEntry->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $salesEntry->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($salesEntry->trashed())
                                    @can('restore', $salesEntry)
                                        <form method="POST" action="{{ route('sales-entries.restore', $salesEntry->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $salesEntry)
                                        <form method="POST" action="{{ route('sales-entries.force-destroy', $salesEntry->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('view', $salesEntry)
                                        <a href="{{ route('sales-entries.show', $salesEntry) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                    @endcan
                                    @can('update', $salesEntry)
                                        <a href="{{ route('sales-entries.edit', $salesEntry) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $salesEntry)
                                        <form method="POST" action="{{ route('sales-entries.destroy', $salesEntry) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No sales entries found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('sales-entries.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\SalesEntry::class)
        <x-modal id="importModal" title="Import Sales Entries">
            <form id="importForm" method="POST" action="{{ route('sales-entries.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: employee_id, customer_code, product_sku, sale_date, quantity, unit_price, discount_amount, remarks.</div>
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
