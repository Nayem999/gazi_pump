@extends('layouts.admin')

@section('title', 'Orders')

@section('breadcrumb')
    <li class="breadcrumb-item active">Orders</li>
@endsection

@section('content')
    <x-filter-bar :action="route('orders.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Dealer or product..." value="{{ $filters['search'] ?? '' }}">
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
            <label class="form-label">Territory</label>
            <select name="territory_id" class="form-select">
                <option value="">All</option>
                @foreach ($territories as $territory)
                    <option value="{{ $territory->id }}" @selected((string) ($filters['territory_id'] ?? '') === (string) $territory->id)>{{ $territory->name }}</option>
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

    <form id="bulkForm" method="POST" action="{{ route('orders.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected orders?">
        @csrf
        <x-data-table
            title="Orders"
            :create-url="auth()->user()->can('create', \App\Models\Order::class) ? route('orders.create') : null"
            create-label="Record Order"
            :export-url="auth()->user()->can('export', \App\Models\Order::class) ? route('orders.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Order::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Order::class) ? route('orders.print', request()->query()) : null"
            :paginator="$orders"
            :total="$total"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Executive</th>
                    <th>Dealer</th>
                    <th>Territory</th>
                    <th>Items</th>
                    <th>Order Date</th>
                    <th>Total</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($orders as $order)
                <tr>
                    <td>
                        @if (! $order->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $order->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        {{ $order->user?->name }}
                        <div class="text-muted small">{{ $order->user?->employee_id }}</div>
                        <div class="small"><x-phone-actions :phone="$order->user?->phone" /></div>
                    </td>
                    <td>
                        @if ($order->dealer && ! $order->dealer->trashed())
                            <a href="{{ route('dealers.show', $order->dealer) }}">{{ $order->dealer->name }}</a>
                        @else
                            {{ $order->dealer?->name }}
                        @endif
                        <div class="text-muted small">{{ $order->dealer?->dealer_code }}</div>
                        <div class="small"><x-phone-actions :phone="$order->dealer?->phone" /></div>
                    </td>
                    <td>{{ $order->dealer?->territory?->name ?? '—' }}</td>
                    <td>
                        <span class="badge text-bg-secondary">{{ $order->items->count() }} item(s)</span>
                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($order->items->pluck('product.name')->filter()->implode(', '), 50) }}</div>
                    </td>
                    <td>{{ $order->order_date->format('M d, Y') }}</td>
                    <td>{{ number_format((float) $order->total_amount, 2) }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($order->trashed())
                                @can('restore', $order)
                                    <form method="POST" action="{{ route('orders.restore', $order->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $order)
                                    <form method="POST" action="{{ route('orders.force-destroy', $order->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('view', $order)
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                @endcan
                                @can('update', $order)
                                    <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $order)
                                    <form method="POST" action="{{ route('orders.destroy', $order) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <td colspan="8" class="text-center text-muted py-4">No orders found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($orders as $order)
                    <div class="col">
                        <x-item-card
                            icon="ti-receipt"
                            icon-color="primary"
                            :title="$order->dealer?->name"
                            :title-url="$order->dealer && ! $order->dealer->trashed() ? route('dealers.show', $order->dealer) : null"
                            :subtitle="$order->items->count().' item(s)'"
                            :status-label="$order->trashed() ? 'Trashed' : null"
                            status-color="danger"
                        >
                            <x-slot:meta>
                                <div>Executive: {{ $order->user?->name }} &middot; <x-phone-actions :phone="$order->user?->phone" /></div>
                                <div>Dealer Phone: <x-phone-actions :phone="$order->dealer?->phone" /></div>
                                <div>Territory: {{ $order->dealer?->territory?->name ?? '—' }}</div>
                                <div>Date: {{ $order->order_date->format('M d, Y') }}</div>
                                <div>{{ \Illuminate\Support\Str::limit($order->items->pluck('product.name')->filter()->implode(', '), 60) }}</div>
                                <div>Total: {{ number_format((float) $order->total_amount, 2) }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $order->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $order->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($order->trashed())
                                    @can('restore', $order)
                                        <form method="POST" action="{{ route('orders.restore', $order->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $order)
                                        <form method="POST" action="{{ route('orders.force-destroy', $order->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('view', $order)
                                        <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                    @endcan
                                    @can('update', $order)
                                        <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $order)
                                        <form method="POST" action="{{ route('orders.destroy', $order) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No orders found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('orders.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Order::class)
        <x-modal id="importModal" title="Import Orders">
            <form id="importForm" method="POST" action="{{ route('orders.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: employee_id, dealer_code, product_sku, order_date, quantity, unit_price, discount_amount, remarks.</div>
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
