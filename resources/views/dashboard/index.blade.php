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
            <x-stat-card icon="ti-building-store" label="Dealers" value="{{ $dealerCount }}" color="info" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-map-pin" label="Territories" value="{{ $territoryCount }}" color="warning" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-package" label="Products" value="{{ $productCount }}" color="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-calendar-check" label="Present Today" value="{{ $presentTodayCount }}" color="success" />
        </div>
    </div>

    <div class="card mb-4 hover-lift">
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

    <div class="card mb-4 hover-lift">
        <div class="card-body">
            <h6 class="mb-3">Attendance Trend &mdash; Last 14 Days</h6>
            <div id="attendanceTrendChart" data-chart-attendance="{{ json_encode($attendanceTrend) }}"></div>
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
