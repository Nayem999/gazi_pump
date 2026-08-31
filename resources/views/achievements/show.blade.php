@extends('layouts.admin')

@section('title', 'Achievement Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achievements.index') }}">Achievement</a></li>
    <li class="breadcrumb-item active">{{ $entry->user->name }} &mdash; {{ $entry->entryDateLabel() }}</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ti ti-trophy display-1 text-secondary mb-2 d-block"></i>
                    <h5 class="mb-0">{{ $entry->user->name }}</h5>
                    <div class="text-muted">{{ $entry->user->employee_id }}</div>
                    <div class="mt-2">
                        <span class="badge text-bg-secondary">{{ $entry->entryDateLabel() }}</span>
                        <span class="badge text-bg-{{ $entry->status->badgeColor() }}">{{ $entry->status->label() }}</span>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        @can('update', $entry)
                            <a href="{{ route('achievements.edit', $entry) }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-pencil me-1"></i>Edit
                            </a>
                        @endcan
                        @if ($entry->status === \App\Enums\ApprovalStatus::Pending)
                            @can('approve', $entry)
                                <form method="POST" action="{{ route('achievements.approve', $entry) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="ti ti-check me-1"></i>Approve</button>
                                </form>
                                <form method="POST" action="{{ route('achievements.reject', $entry) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-x me-1"></i>Reject</button>
                                </form>
                            @endcan
                        @endif
                    </div>
                    @if ($entry->approvedBy)
                        <div class="text-muted small mt-3">{{ $entry->status->label() }} by {{ $entry->approvedBy->name }} &middot; {{ $entry->approved_at?->format('M d, Y H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Achievement</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Order Value Achieved</div>
                            <div class="fs-5 fw-semibold">{{ number_format((float) $entry->order_value_achieved, 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Collection Achieved</div>
                            <div class="fs-5 fw-semibold">{{ number_format((float) $entry->collection_achieved, 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Quantity Achieved</div>
                            <div class="fs-5 fw-semibold">{{ $entry->quantity_achieved }}</div>
                        </div>
                    </div>

                    @if ($entry->notes)
                        <hr>
                        <h6 class="mb-2">Notes</h6>
                        <p class="mb-0">{{ $entry->notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        @if ($entry->isProductWise())
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Product-wise Achievement</div>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Order Achieved</th>
                                    <th>Collection Achieved</th>
                                    <th>Quantity Achieved</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($entry->items as $item)
                                    <tr>
                                        <td>
                                            {{ $item->product?->name }}
                                            <div class="text-muted small">{{ $item->product?->sku }}</div>
                                        </td>
                                        <td>{{ number_format((float) $item->order_achieved, 2) }}</td>
                                        <td>{{ number_format((float) $item->collection_achieved, 2) }}</td>
                                        <td>{{ $item->quantity_achieved }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-end fw-semibold">Total</td>
                                    <td class="fw-semibold">{{ number_format((float) $entry->order_value_achieved, 2) }}</td>
                                    <td class="fw-semibold">{{ number_format((float) $entry->collection_achieved, 2) }}</td>
                                    <td class="fw-semibold">{{ $entry->quantity_achieved }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
