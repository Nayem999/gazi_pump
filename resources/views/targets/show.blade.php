@extends('layouts.admin')

@section('title', 'Target Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('targets.index') }}">Targets</a></li>
    <li class="breadcrumb-item active">{{ $target->user->name }} &mdash; {{ $target->periodLabel() }}</li>
@endsection

@php
    $achievement = $target->achievement;
@endphp

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ti ti-target-arrow display-1 text-secondary mb-2 d-block"></i>
                    <h5 class="mb-0">{{ $target->user->name }}</h5>
                    <div class="text-muted">{{ $target->user->employee_id }}</div>
                    <div class="mt-2">
                        <span class="badge text-bg-secondary">{{ $target->periodLabel() }}</span>
                        @if ($achievement)
                            <span class="badge text-bg-{{ $achievement->grade->badgeColor() }}">{{ $achievement->grade->value }} &middot; {{ $achievement->grade->label() }}</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        @can('update', $target)
                            <a href="{{ route('targets.edit', $target) }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-pencil me-1"></i>Edit
                            </a>
                            <form method="POST" action="{{ route('targets.recalculate', $target) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-info btn-sm">
                                    <i class="ti ti-refresh me-1"></i>Recalculate
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Achievement Breakdown</h6>

                    @if ($achievement)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Sales Value</span>
                                <span>{{ number_format((float) $achievement->sales_achieved, 2) }} / {{ number_format((float) $target->sales_value_target, 2) }} ({{ number_format((float) $achievement->sales_pct, 1) }}%)</span>
                            </div>
                            <div class="progress" style="height:8px">
                                <div class="progress-bar bg-primary" style="width: {{ min(100, (float) $achievement->sales_pct) }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Collection</span>
                                <span>{{ number_format((float) $achievement->collection_achieved, 2) }} / {{ number_format((float) $target->collection_target, 2) }} ({{ number_format((float) $achievement->collection_pct, 1) }}%)</span>
                            </div>
                            <div class="progress" style="height:8px">
                                <div class="progress-bar bg-success" style="width: {{ min(100, (float) $achievement->collection_pct) }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Quantity (units)</span>
                                <span>{{ $achievement->quantity_achieved }} / {{ $target->quantity_target }} ({{ number_format((float) $achievement->quantity_pct, 1) }}%)</span>
                            </div>
                            <div class="progress" style="height:8px">
                                <div class="progress-bar bg-info" style="width: {{ min(100, (float) $achievement->quantity_pct) }}%"></div>
                            </div>
                        </div>

                        <div class="mb-1">
                            <div class="d-flex justify-content-between small mb-1">
                                <strong>Overall</strong>
                                <strong>{{ number_format((float) $achievement->overall_pct, 1) }}%</strong>
                            </div>
                            <div class="progress" style="height:10px">
                                <div class="progress-bar bg-{{ $achievement->grade->badgeColor() }}" style="width: {{ min(100, (float) $achievement->overall_pct) }}%"></div>
                            </div>
                        </div>

                        <div class="text-muted small mt-3">Last calculated {{ $achievement->calculated_at->format('M d, Y H:i') }}</div>
                    @else
                        <p class="text-muted mb-0">No achievement calculated yet.</p>
                    @endif

                    @if ($target->notes)
                        <hr>
                        <h6 class="mb-2">Notes</h6>
                        <p class="mb-0">{{ $target->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
