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
    </div>
@endsection
