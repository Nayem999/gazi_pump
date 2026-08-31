@extends('layouts.admin')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-users" label="Active Users" value="{{ $activeUserCount }}" color="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-id-badge-2" label="Sales Executives" value="{{ $salesExecutiveCount }}" color="success" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-building-store" label="Dealers" value="{{ $dealerCount }}" color="warning" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-map-pin" label="Territories" value="{{ $territoryCount }}" color="info" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-package" label="Products" value="{{ $productCount }}" color="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-calendar-check" label="Present Today" value="{{ $presentTodayCount }}" color="success" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-receipt" label="Today's Sales" value="৳ {{ number_format($todaysSalesAmount, 2) }}" color="warning" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-cash" label="Today's Collection" value="৳ {{ number_format($todaysCollectionAmount, 2) }}" color="info" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card mb-4 hover-lift h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h6 class="mb-0">Order vs Collection &mdash; Last 6 Months</h6>
                        @if ($scopedToOwnTerritories)
                            <span class="badge text-bg-secondary">Your territories only</span>
                        @endif
                    </div>
                    <div class="text-muted small mb-3">Each bar split by approval status.</div>
                    <div id="orderVsCollectionChart" data-chart-order-vs-collection="{{ json_encode($orderVsCollectionTrend) }}"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4 hover-lift h-100">
                <div class="card-body">
                    <h6 class="mb-3">Attendance Trend &mdash; Last 14 Days</h6>
                    <div id="attendanceTrendChart" data-chart-attendance="{{ json_encode($attendanceTrend) }}"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 hover-lift">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0">Payment Mode Summary</h6>
                <span class="text-muted small">Today &middot; Approved only</span>
            </div>
            <div class="row align-items-center g-3">
                <div class="col-md-5">
                    <div id="paymentModeChart"
                         data-labels="{{ json_encode(collect($paymentModeSummary)->pluck('label')) }}"
                         data-series="{{ json_encode(collect($paymentModeSummary)->pluck('amount')) }}"
                         data-colors="{{ json_encode(collect($paymentModeSummary)->pluck('color')) }}"
                         data-total-label="৳ {{ number_format($todaysCollectionAmount, 2) }}"
                    ></div>
                </div>
                <div class="col-md-7">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            @foreach ($paymentModeSummary as $mode)
                                <tr>
                                    <td style="width:1.5rem"><span class="d-inline-block rounded" style="width:14px;height:14px;background-color:{{ $mode['color'] }}"></span></td>
                                    <td>{{ $mode['label'] }}</td>
                                    <td class="text-end">
                                        ৳ {{ number_format($mode['amount'], 2) }}
                                        <span class="text-muted small">({{ number_format($mode['percentage'], 2) }}%)</span>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="border-top fw-semibold">
                                <td></td>
                                <td>Total</td>
                                <td class="text-end">৳ {{ number_format($todaysCollectionAmount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-data-table title="Recent Activity">
        <x-slot:thead>
            <tr>
                <th>Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Description</th>
            </tr>
        </x-slot:thead>

        @forelse ($recentActivity as $activity)
            <tr>
                <td>{{ $activity->created_at->diffForHumans() }}</td>
                <td>{{ $activity->causer?->name ?? 'System' }}</td>
                <td>
                    <span class="badge text-bg-{{ match ($activity->event) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'secondary',
                    } }}">
                        {{ ucfirst($activity->event ?? $activity->description) }}
                    </span>
                </td>
                <td>{{ \Illuminate\Support\Str::headline(\Illuminate\Support\Str::singular($activity->log_name)) }} #{{ $activity->subject_id }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted py-4">No recent activity.</td>
            </tr>
        @endforelse
    </x-data-table>
@endsection
