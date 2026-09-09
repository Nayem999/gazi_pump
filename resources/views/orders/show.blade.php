@extends('layouts.admin')

@section('title', 'Order Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
    <li class="breadcrumb-item active">{{ $order->dealer->name }} &mdash; {{ $order->order_date->format('d M Y') }}</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ti ti-building-store display-1 text-secondary mb-2 d-block"></i>
                    <h5 class="mb-0">
                        @if (! $order->dealer->trashed())
                            <a href="{{ route('dealers.show', $order->dealer) }}">{{ $order->dealer->name }}</a>
                        @else
                            {{ $order->dealer->name }}
                        @endif
                    </h5>
                    <div class="text-muted">{{ $order->dealer->dealer_code }}</div>
                    <div class="mt-2 d-flex flex-wrap justify-content-center gap-1">
                        <span class="badge text-bg-primary">{{ $order->items->count() }} item(s)</span>
                        <span class="badge text-bg-{{ $order->status->badgeColor() }}">{{ $order->status->label() }}</span>
                    </div>
                    @if ($order->status === \App\Enums\ApprovalStatus::Pending)
                        @can('approve', $order)
                            <div class="d-flex justify-content-center gap-2 mt-2 d-print-none">
                                <form method="POST" action="{{ route('orders.approve', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-check me-1"></i>Approve</button>
                                </form>
                                <form method="POST" action="{{ route('orders.reject', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-x me-1"></i>Reject</button>
                                </form>
                            </div>
                        @endcan
                    @endif
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-2 d-print-none">
                        @can('update', $order)
                            <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-pencil me-1"></i>Edit
                            </a>
                        @endcan
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="ti ti-printer me-1"></i>Print
                        </button>
                        <a href="{{ route('orders.download-pdf', $order) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-file-download me-1"></i>Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Order Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Sales Executive</dt>
                        <dd class="col-sm-8">{{ $order->user->name }} ({{ $order->user->employee_id }})</dd>

                        <dt class="col-sm-4">Executive Phone</dt>
                        <dd class="col-sm-8"><x-phone-actions :phone="$order->user->phone" /></dd>

                        <dt class="col-sm-4">Dealer Phone</dt>
                        <dd class="col-sm-8"><x-phone-actions :phone="$order->dealer->phone" /></dd>

                        <dt class="col-sm-4">Retailer</dt>
                        <dd class="col-sm-8">{{ $order->retailer?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Order Date</dt>
                        <dd class="col-sm-8">{{ $order->order_date->format('d M Y') }}</dd>

                        <dt class="col-sm-4">Total Amount</dt>
                        <dd class="col-sm-8">{{ number_format((float) $order->total_amount, 2) }}</dd>

                        <dt class="col-sm-4">Remarks</dt>
                        <dd class="col-sm-8">{{ $order->remarks ?? '—' }}</dd>

                        <dt class="col-sm-4">Approval Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge text-bg-{{ $order->status->badgeColor() }}">{{ $order->status->label() }}</span>
                            @if ($order->approvedBy)
                                <span class="text-muted small">by {{ $order->approvedBy->name }} on {{ $order->approved_at?->format('d M Y, h:i A') }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Tally Sync</dt>
                        <dd class="col-sm-8">
                            <span class="badge text-bg-{{ $order->sync_status->badgeColor() }}">{{ $order->sync_status->label() }}</span>
                            @if ($order->tally_voucher_number)
                                <span class="text-muted small">Voucher {{ $order->tally_voucher_number }}</span>
                            @elseif ($order->sync_error)
                                <span class="text-danger small d-block">{{ $order->sync_error }}</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">Line Items</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Discount</th>
                                <th>Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->product?->name }}
                                        <div class="text-muted small">{{ $item->product?->sku }}</div>
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td>{{ number_format((float) $item->discount_amount, 2) }}</td>
                                    <td class="fw-semibold">{{ number_format((float) $item->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-semibold">Grand Total</td>
                                <td class="fw-semibold">{{ number_format((float) $order->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{--
            Depot Allocation (spec §12/§13): one order line can draw from
            several depots (Split Depot Fulfillment) — each already-made
            allocation is listed, plus a form to add another one from any
            depot with sellable stock (findAvailableDepots() already
            excludes depots with none, so every option here is real).
        --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header">Depot Allocation</div>
                <div class="card-body">
                    @foreach ($order->items as $item)
                        @php $lineStatus = app(\App\Services\DepotAllocationService::class)->lineStatus($item); @endphp
                        <div class="mb-4 pb-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ $item->product?->name }}</strong>
                                    <span class="text-muted small">— requested {{ $item->quantity }}</span>
                                </div>
                                <span class="badge text-bg-{{ $lineStatus->badgeColor() }}">{{ $lineStatus->label() }}</span>
                            </div>

                            @if ($item->depotAllocations->isNotEmpty())
                                <table class="table table-sm mb-2">
                                    <thead>
                                        <tr>
                                            <th>Depot</th>
                                            <th>Allocated Qty</th>
                                            <th>Alternative?</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($item->depotAllocations->where('allocation_status', '!=', 'rejected') as $allocation)
                                            <tr>
                                                <td>{{ $allocation->depot->name }}</td>
                                                <td>{{ number_format((float) $allocation->allocated_qty, 2) }}</td>
                                                <td>{{ $allocation->is_alternative_depot ? 'Yes' : 'No' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @can('update', $order)
                                @php $depotOptions = $availableDepotsByProduct[$item->id] ?? collect(); @endphp
                                @if ($depotOptions->isNotEmpty())
                                    <form method="POST" action="{{ route('orders.items.allocate', [$order, $item]) }}" class="row g-2 align-items-end">
                                        @csrf
                                        <div class="col-md-5">
                                            <label class="form-label small mb-1">Depot</label>
                                            <select name="depot_id" class="form-select form-select-sm" required>
                                                @foreach ($depotOptions as $stock)
                                                    <option value="{{ $stock->depot_id }}">{{ $stock->depot->name }} ({{ number_format($stock->sellableQty(), 2) }} available)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small mb-1">Quantity</label>
                                            <input type="number" name="quantity" class="form-control form-control-sm" min="0.01" step="0.01" value="{{ $item->quantity }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="is_alternative_depot" value="1" class="form-check-input" id="altDepot{{ $item->id }}">
                                                <label class="form-check-label small" for="altDepot{{ $item->id }}">Alternative depot</label>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="submit" class="btn btn-outline-primary btn-sm"><i class="ti ti-plus"></i></button>
                                        </div>
                                    </form>
                                @else
                                    <p class="text-danger small mb-0">No depot has sellable stock for this product.</p>
                                @endif
                            @endcan
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">Delivery / Challan</div>
                <div class="card-body">
                    @if ($order->delivery)
                        <dl class="row mb-0">
                            <dt class="col-sm-3">Status</dt>
                            <dd class="col-sm-9">
                                <span class="badge text-bg-{{ $order->delivery->status->badgeColor() }}">{{ $order->delivery->status->label() }}</span>
                            </dd>

                            <dt class="col-sm-3">Delivery Date</dt>
                            <dd class="col-sm-9">{{ $order->delivery->delivery_date->format('d M Y') }}</dd>

                            <dt class="col-sm-3">Vehicle</dt>
                            <dd class="col-sm-9">{{ $order->delivery->vehicle?->registration_number ?? '—' }}</dd>

                            <dt class="col-sm-3">Driver</dt>
                            <dd class="col-sm-9">{{ $order->delivery->driver?->name ?? '—' }}</dd>

                            <dt class="col-sm-3">Tally Sync</dt>
                            <dd class="col-sm-9">
                                <span class="badge text-bg-{{ $order->delivery->sync_status->badgeColor() }}">{{ $order->delivery->sync_status->label() }}</span>
                                @if ($order->delivery->tally_delivery_number)
                                    <span class="text-muted small">Challan {{ $order->delivery->tally_delivery_number }}</span>
                                @elseif ($order->delivery->sync_error)
                                    <span class="text-danger small d-block">{{ $order->delivery->sync_error }}</span>
                                @endif
                            </dd>
                        </dl>

                        @if ($order->delivery->status === \App\Enums\DeliveryStatus::Dispatched)
                            @can('deliveries.add')
                                <form method="POST" action="{{ route('deliveries.deliver', $order->delivery) }}" class="mt-3 d-print-none">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-check me-1"></i>Mark Delivered</button>
                                </form>
                            @endcan
                        @endif
                    @elseif ($order->status !== \App\Enums\ApprovalStatus::Approved)
                        <p class="text-muted mb-0">This order must be approved before it can be dispatched.</p>
                    @else
                        @php
                            $depotAllocationService = app(\App\Services\DepotAllocationService::class);
                            $fullyAllocated = $order->items->every(fn ($item) => $depotAllocationService->lineStatus($item) === \App\Enums\AllocationStatus::Allocated);
                        @endphp

                        @if (! $fullyAllocated)
                            <p class="text-muted mb-0">Every line must be fully depot-allocated before this order can be dispatched.</p>
                        @elseif (! auth()->user()->can('deliveries.add'))
                            <p class="text-muted mb-0">Ready to dispatch — waiting on a manager.</p>
                        @else
                            <form method="POST" action="{{ route('orders.dispatch', $order) }}" class="row g-2 align-items-end d-print-none">
                                @csrf
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Vehicle</label>
                                    <select name="vehicle_id" class="form-select form-select-sm">
                                        <option value="">— None —</option>
                                        @foreach ($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Driver</label>
                                    <select name="driver_id" class="form-select form-select-sm">
                                        <option value="">— None —</option>
                                        @foreach ($drivers as $driver)
                                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Delivery Date</label>
                                    <input type="date" name="delivery_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="ti ti-truck-delivery me-1"></i>Dispatch</button>
                                </div>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">Sales Returns</div>
                <div class="card-body">
                    @if ($order->salesReturns->isNotEmpty())
                        <table class="table table-sm mb-3">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Status</th>
                                    <th>Sync</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->salesReturns as $return)
                                    <tr>
                                        <td class="font-monospace small">{{ $return->external_reference }}</td>
                                        <td><span class="badge text-bg-{{ $return->status->badgeColor() }}">{{ $return->status->label() }}</span></td>
                                        <td><span class="badge text-bg-{{ $return->sync_status->badgeColor() }}">{{ $return->sync_status->label() }}</span></td>
                                        <td><a href="{{ route('sales-returns.show', $return) }}" class="btn btn-outline-secondary btn-sm">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if ($order->status === \App\Enums\ApprovalStatus::Approved)
                        @can('create', \App\Models\SalesReturn::class)
                            @php $returnableItems = $order->items->filter(fn ($item) => ($returnableQtyByItem[$item->id] ?? 0) > 0); @endphp

                            @if ($returnableItems->isNotEmpty())
                                <form method="POST" action="{{ route('orders.returns.store', $order) }}">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label small mb-1">Reason</label>
                                        <input type="text" name="reason" class="form-control form-control-sm">
                                    </div>
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Left to Return</th>
                                                <th style="width:160px">Return Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($returnableItems as $item)
                                                <tr>
                                                    <td>{{ $item->product?->name }}</td>
                                                    <td>{{ $returnableQtyByItem[$item->id] }}</td>
                                                    <td>
                                                        <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                                        <input type="number" name="items[{{ $loop->index }}][quantity]" class="form-control form-control-sm" min="0" max="{{ $returnableQtyByItem[$item->id] }}" step="0.01" value="0">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-rotate-2 me-1"></i>Request Return</button>
                                </form>
                            @else
                                <p class="text-muted small mb-0">Every line has already been fully returned.</p>
                            @endif
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
