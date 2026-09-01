@extends('layouts.admin')

@section('title', 'Cash Handover Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cash-handovers.index') }}">Cash Handover</a></li>
    <li class="breadcrumb-item active">{{ $cashHandover->user?->name }} &mdash; {{ $cashHandover->handover_date->format('d M Y') }}</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ti ti-hand-move display-1 text-secondary mb-2 d-block"></i>
                    <h5 class="mb-0">{{ $cashHandover->user?->name }}</h5>
                    <div class="text-muted">{{ $cashHandover->user?->employee_id }}</div>
                    <div class="mt-2">
                        <span class="badge text-bg-{{ $cashHandover->status->badgeColor() }}">{{ $cashHandover->status->label() }}</span>
                    </div>

                    @if ($cashHandover->status === \App\Enums\CashHandoverStatus::Pending)
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            @can('confirm', $cashHandover)
                                <form method="POST" action="{{ route('cash-handovers.confirm', $cashHandover) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-check me-1"></i>Confirm</button>
                                </form>
                                <form method="POST" action="{{ route('cash-handovers.reject', $cashHandover) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-x me-1"></i>Reject</button>
                                </form>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Amount</dt>
                        <dd class="col-sm-8">৳ {{ number_format((float) $cashHandover->amount, 2) }}</dd>

                        <dt class="col-sm-4">Handover Date</dt>
                        <dd class="col-sm-8">{{ $cashHandover->handover_date->format('d M Y') }}</dd>

                        <dt class="col-sm-4">Confirmed / Rejected By</dt>
                        <dd class="col-sm-8">{{ $cashHandover->confirmedBy?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Confirmed / Rejected At</dt>
                        <dd class="col-sm-8">{{ $cashHandover->confirmed_at?->format('d M Y, h:i A') ?? '—' }}</dd>

                        <dt class="col-sm-4">Remarks</dt>
                        <dd class="col-sm-8">{{ $cashHandover->remarks ?? '—' }}</dd>

                        <dt class="col-sm-4">Cash in Hand (current)</dt>
                        <dd class="col-sm-8">
                            ৳ {{ number_format($cashInHand, 2) }}
                            @if ($dailyLimit)
                                <span class="text-muted small">/ limit ৳ {{ number_format($dailyLimit, 2) }}</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
