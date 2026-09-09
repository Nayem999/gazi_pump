@extends('layouts.admin')

@section('title', 'Sales Return Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales-returns.index') }}">Sales Returns</a></li>
    <li class="breadcrumb-item active">{{ $salesReturn->external_reference }}</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Return Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Reference</dt>
                        <dd class="col-sm-8 font-monospace small">{{ $salesReturn->external_reference }}</dd>

                        <dt class="col-sm-4">Order</dt>
                        <dd class="col-sm-8"><a href="{{ route('orders.show', $salesReturn->order) }}">#{{ $salesReturn->order_id }}</a></dd>

                        <dt class="col-sm-4">Dealer</dt>
                        <dd class="col-sm-8">{{ $salesReturn->dealer->name }}</dd>

                        <dt class="col-sm-4">Requested By</dt>
                        <dd class="col-sm-8">{{ $salesReturn->user->name }}</dd>

                        <dt class="col-sm-4">Reason</dt>
                        <dd class="col-sm-8">{{ $salesReturn->reason ?? '—' }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge text-bg-{{ $salesReturn->status->badgeColor() }}">{{ $salesReturn->status->label() }}</span>
                            @if ($salesReturn->approvedBy)
                                <span class="text-muted small">by {{ $salesReturn->approvedBy->name }} on {{ $salesReturn->approved_at?->format('d M Y, h:i A') }}</span>
                            @endif
                        </dd>

                        @if ($salesReturn->vehicle || $salesReturn->driver)
                            <dt class="col-sm-4">Vehicle / Driver</dt>
                            <dd class="col-sm-8">{{ $salesReturn->vehicle?->registration_number ?? '—' }} / {{ $salesReturn->driver?->name ?? '—' }}</dd>
                        @endif

                        @if ($salesReturn->receivingDepot)
                            <dt class="col-sm-4">Received At</dt>
                            <dd class="col-sm-8">{{ $salesReturn->receivingDepot->name }} by {{ $salesReturn->receivedBy?->name }} on {{ $salesReturn->received_at?->format('d M Y, h:i A') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Tally Sync</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge text-bg-{{ $salesReturn->sync_status->badgeColor() }}">{{ $salesReturn->sync_status->label() }}</span>
                        </dd>

                        <dt class="col-sm-4">Credit Note No.</dt>
                        <dd class="col-sm-8">{{ $salesReturn->tally_credit_note_number ?? '—' }}</dd>

                        @if ($salesReturn->sync_error)
                            <dt class="col-sm-4">Error</dt>
                            <dd class="col-sm-8 text-danger">{{ $salesReturn->sync_error }}</dd>
                        @endif
                    </dl>

                    <div class="d-flex flex-wrap gap-2 mt-3 d-print-none">
                        @if ($salesReturn->status === \App\Enums\SalesReturnStatus::Requested)
                            @can('approve', $salesReturn)
                                <form method="POST" action="{{ route('sales-returns.approve', $salesReturn) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-check me-1"></i>Approve</button>
                                </form>
                                <form method="POST" action="{{ route('sales-returns.reject', $salesReturn) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-x me-1"></i>Reject</button>
                                </form>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($salesReturn->status === \App\Enums\SalesReturnStatus::Approved)
            @can('update', $salesReturn)
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">Dispatch Return</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('sales-returns.dispatch', $salesReturn) }}" class="row g-2 align-items-end">
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
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Driver</label>
                                    <select name="driver_id" class="form-select form-select-sm">
                                        <option value="">— None —</option>
                                        @foreach ($drivers as $driver)
                                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-truck-delivery me-1"></i>Dispatch</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan
        @endif

        @if ($salesReturn->status === \App\Enums\SalesReturnStatus::Dispatched)
            @can('update', $salesReturn)
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">Confirm Depot Receiving</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('sales-returns.receive', $salesReturn) }}">
                                @csrf
                                <div class="mb-3 col-md-6">
                                    <label class="form-label small mb-1">Receiving Depot</label>
                                    <select name="receiving_depot_id" class="form-select form-select-sm" required>
                                        <option value="">— Select —</option>
                                        @foreach ($depots as $depot)
                                            <option value="{{ $depot->id }}">{{ $depot->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Requested Qty</th>
                                            <th>Received Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($salesReturn->items as $item)
                                            <tr>
                                                <td>{{ $item->product?->name }}</td>
                                                <td>{{ $item->requested_qty }}</td>
                                                <td>
                                                    <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
                                                    <input type="number" name="items[{{ $loop->index }}][received_qty]" class="form-control form-control-sm" min="0" step="0.01" value="{{ $item->requested_qty }}" required>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-check me-1"></i>Confirm Received &amp; Sync to Tally</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan
        @endif

        <div class="col-12">
            <div class="card">
                <div class="card-header">Items</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Requested Qty</th>
                                <th>Received Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($salesReturn->items as $item)
                                <tr>
                                    <td>{{ $item->product?->name }}</td>
                                    <td>{{ $item->requested_qty }}</td>
                                    <td>{{ $item->received_qty ?? '—' }}</td>
                                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td>{{ number_format((float) $item->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
