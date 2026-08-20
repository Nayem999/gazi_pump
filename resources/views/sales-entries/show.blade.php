@extends('layouts.admin')

@section('title', 'Sale Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales-entries.index') }}">Sales Entry</a></li>
    <li class="breadcrumb-item active">{{ $salesEntry->customer->name }} &mdash; {{ $salesEntry->sale_date->format('M d, Y') }}</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ti ti-building-store display-1 text-secondary mb-2 d-block"></i>
                    <h5 class="mb-0">{{ $salesEntry->customer->name }}</h5>
                    <div class="text-muted">{{ $salesEntry->customer->customer_code }}</div>
                    <div class="mt-2">
                        <span class="badge text-bg-primary">{{ $salesEntry->items->count() }} item(s)</span>
                    </div>
                    @can('update', $salesEntry)
                        <a href="{{ route('sales-entries.edit', $salesEntry) }}" class="btn btn-outline-primary btn-sm mt-3">
                            <i class="ti ti-pencil me-1"></i>Edit
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Sale Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Sales Executive</dt>
                        <dd class="col-sm-8">{{ $salesEntry->user->name }} ({{ $salesEntry->user->employee_id }})</dd>

                        <dt class="col-sm-4">Sale Date</dt>
                        <dd class="col-sm-8">{{ $salesEntry->sale_date->format('M d, Y') }}</dd>

                        <dt class="col-sm-4">Total Amount</dt>
                        <dd class="col-sm-8">{{ number_format((float) $salesEntry->total_amount, 2) }}</dd>

                        <dt class="col-sm-4">Remarks</dt>
                        <dd class="col-sm-8">{{ $salesEntry->remarks ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">Line Items</div>
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
                            @foreach ($salesEntry->items as $item)
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
                                <td class="fw-semibold">{{ number_format((float) $salesEntry->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
