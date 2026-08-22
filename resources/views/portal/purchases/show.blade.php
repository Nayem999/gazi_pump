@extends('layouts.portal-account')

@section('title', 'Purchase Detail')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Purchase &mdash; {{ $order->order_date->format('M d, Y') }}</h1>
        <a href="{{ route('portal.purchases.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i>Back to Purchases
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Date</dt>
                <dd class="col-sm-9">{{ $order->order_date->format('M d, Y') }}</dd>

                <dt class="col-sm-3">Items</dt>
                <dd class="col-sm-9">{{ $order->items->count() }}</dd>

                <dt class="col-sm-3">Total Amount</dt>
                <dd class="col-sm-9 fw-semibold">{{ number_format((float) $order->total_amount, 2) }}</dd>

                @if ($order->remarks)
                    <dt class="col-sm-3">Remarks</dt>
                    <dd class="col-sm-9">{{ $order->remarks }}</dd>
                @endif
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">Items Purchased</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
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
                                {{ $item->product?->name ?? 'Unknown product' }}
                                @if ($item->product?->sku)
                                    <div class="text-muted small">{{ $item->product->sku }}</div>
                                @endif
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
@endsection
