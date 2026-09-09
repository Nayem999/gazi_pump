@extends('layouts.admin')

@section('title', 'Delivery Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('deliveries.index') }}">Deliveries</a></li>
    <li class="breadcrumb-item active">{{ $delivery->external_reference }}</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Delivery Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Reference</dt>
                        <dd class="col-sm-8 font-monospace small">{{ $delivery->external_reference }}</dd>

                        <dt class="col-sm-4">Order</dt>
                        <dd class="col-sm-8"><a href="{{ route('orders.show', $delivery->order) }}">#{{ $delivery->order_id }}</a></dd>

                        <dt class="col-sm-4">Dealer</dt>
                        <dd class="col-sm-8">{{ $delivery->order->dealer->name }}</dd>

                        <dt class="col-sm-4">Vehicle</dt>
                        <dd class="col-sm-8">{{ $delivery->vehicle?->registration_number ?? '—' }}</dd>

                        <dt class="col-sm-4">Driver</dt>
                        <dd class="col-sm-8">{{ $delivery->driver?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Delivery Date</dt>
                        <dd class="col-sm-8">{{ $delivery->delivery_date->format('d M Y') }}</dd>

                        <dt class="col-sm-4">Dispatched By</dt>
                        <dd class="col-sm-8">{{ $delivery->dispatchedBy?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge text-bg-{{ $delivery->status->badgeColor() }}">{{ $delivery->status->label() }}</span>
                            @if ($delivery->delivered_at)
                                <span class="text-muted small">on {{ $delivery->delivered_at->format('d M Y, h:i A') }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Remarks</dt>
                        <dd class="col-sm-8">{{ $delivery->remarks ?? '—' }}</dd>
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
                            <span class="badge text-bg-{{ $delivery->sync_status->badgeColor() }}">{{ $delivery->sync_status->label() }}</span>
                        </dd>

                        <dt class="col-sm-4">Challan No.</dt>
                        <dd class="col-sm-8">{{ $delivery->tally_delivery_number ?? '—' }}</dd>

                        @if ($delivery->sync_error)
                            <dt class="col-sm-4">Error</dt>
                            <dd class="col-sm-8 text-danger">{{ $delivery->sync_error }}</dd>
                        @endif
                    </dl>

                    @if ($delivery->status === \App\Enums\DeliveryStatus::Dispatched)
                        @can('deliveries.add')
                            <form method="POST" action="{{ route('deliveries.deliver', $delivery) }}" class="mt-3 d-print-none">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-check me-1"></i>Mark Delivered</button>
                            </form>
                        @endcan
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">Items</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($delivery->order->items as $item)
                                <tr>
                                    <td>{{ $item->product?->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
