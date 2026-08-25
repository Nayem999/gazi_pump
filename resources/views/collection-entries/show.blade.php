@extends('layouts.admin')

@section('title', 'Collection Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('collection-entries.index') }}">Collection Entries</a></li>
    <li class="breadcrumb-item active">{{ $collectionEntry->dealer?->name }} &mdash; {{ $collectionEntry->collection_date->format('M d, Y') }}</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ti ti-cash display-1 text-secondary mb-2 d-block"></i>
                    <h5 class="mb-0">
                        @if ($collectionEntry->dealer && ! $collectionEntry->dealer->trashed())
                            <a href="{{ route('dealers.show', $collectionEntry->dealer) }}">{{ $collectionEntry->dealer->name }}</a>
                        @else
                            {{ $collectionEntry->dealer?->name }}
                        @endif
                    </h5>
                    <div class="text-muted">{{ $collectionEntry->dealer?->dealer_code }}</div>
                    <div class="mt-2">
                        <span class="badge text-bg-secondary">{{ $collectionEntry->payment_method->label() }}</span>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-3 d-print-none">
                        @can('update', $collectionEntry)
                            <a href="{{ route('collection-entries.edit', $collectionEntry) }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-pencil me-1"></i>Edit
                            </a>
                        @endcan
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="ti ti-printer me-1"></i>Print
                        </button>
                        <a href="{{ route('collection-entries.download-pdf', $collectionEntry) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-file-download me-1"></i>Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Collection Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Sales Executive</dt>
                        <dd class="col-sm-8">{{ $collectionEntry->user->name }} ({{ $collectionEntry->user->employee_id }})</dd>

                        <dt class="col-sm-4">Executive Phone</dt>
                        <dd class="col-sm-8"><x-phone-actions :phone="$collectionEntry->user->phone" /></dd>

                        <dt class="col-sm-4">Dealer Phone</dt>
                        <dd class="col-sm-8"><x-phone-actions :phone="$collectionEntry->dealer?->phone" /></dd>

                        <dt class="col-sm-4">Collection Date</dt>
                        <dd class="col-sm-8">{{ $collectionEntry->collection_date->format('M d, Y') }}</dd>

                        <dt class="col-sm-4">Amount</dt>
                        <dd class="col-sm-8 fw-semibold">{{ number_format((float) $collectionEntry->amount, 2) }}</dd>

                        <dt class="col-sm-4">Payment Method</dt>
                        <dd class="col-sm-8">{{ $collectionEntry->payment_method->label() }}</dd>

                        <dt class="col-sm-4">Reference No.</dt>
                        <dd class="col-sm-8">{{ $collectionEntry->reference_no ?? '—' }}</dd>

                        @if ($collectionEntry->chequeImageUrl())
                            <dt class="col-sm-4">Cheque Image</dt>
                            <dd class="col-sm-8">
                                <a href="{{ $collectionEntry->chequeImageUrl() }}" target="_blank">
                                    <img src="{{ $collectionEntry->chequeImageUrl() }}" class="rounded" style="height:100px">
                                </a>
                            </dd>
                        @endif

                        <dt class="col-sm-4">Remarks</dt>
                        <dd class="col-sm-8">{{ $collectionEntry->remarks ?? '—' }}</dd>

                        <dt class="col-sm-4">Recorded</dt>
                        <dd class="col-sm-8">{{ $collectionEntry->created_at?->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
