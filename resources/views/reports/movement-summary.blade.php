@extends('layouts.admin')

@section('title', 'Movement Summary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Movement Summary</li>
@endsection

@php
    // "Xh Ym" for a duration in seconds, "—" when there's nothing to show
    // (e.g. no attendance record yet, or the executive hasn't checked out).
    $formatDuration = function (?int $seconds) {
        if ($seconds === null) {
            return '—';
        }

        return intdiv($seconds, 3600).'h '.intdiv($seconds % 3600, 60).'m';
    };
@endphp

@section('content')
    <x-filter-bar :action="route('reports.movement-summary')">
        <div class="col-md-4">
            <label class="form-label">Executive</label>
            <select name="user_id" class="form-select" {{ auth()->user()->isSalesExecutiveOnly() ? 'disabled' : '' }}>
                @foreach ($executives as $executive)
                    <option value="{{ $executive->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="{{ $filters['date'] }}">
        </div>
    </x-filter-bar>

    @if (! $summary)
        <div class="text-center text-muted py-5">
            <i class="ti ti-user-off display-4 d-block mb-2"></i>
            No executive available to report on.
        </div>
    @else
        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="mb-0">Daily Activity Report</h6>
                    <div class="text-muted small">{{ $summary->user->name }} ({{ $summary->user->employee_id }}) &mdash; {{ $summary->date->format('d M Y') }}</div>
                </div>
                <a href="{{ route('reports.movement-summary.print', request()->query()) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                    <i class="ti ti-printer me-1"></i>Print
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <x-stat-card icon="ti-clock-hour-4" label="Working Hours" value="{{ $formatDuration($summary->working_seconds) }}" color="primary" />
            </div>
            <div class="col-6 col-lg-3">
                <x-stat-card icon="ti-walk" label="Active Movement" value="{{ $formatDuration($summary->active_seconds) }}" color="success" />
            </div>
            <div class="col-6 col-lg-3">
                <x-stat-card icon="ti-users" label="Customer Visits" value="{{ $summary->visits_count }}" color="info" />
            </div>
            <div class="col-6 col-lg-3">
                <x-stat-card icon="ti-map-pin-share" label="Distance Travelled" value="{{ $summary->distance_km }} km" color="warning" />
            </div>
            <div class="col-6 col-lg-3">
                <x-stat-card icon="ti-hourglass" label="Total Visit Time" value="{{ $formatDuration($summary->visit_seconds) }}" color="primary" />
            </div>
            <div class="col-6 col-lg-3">
                <x-stat-card icon="ti-armchair" label="Idle Time" value="{{ $formatDuration($summary->idle_seconds) }}" color="secondary" />
            </div>
            <div class="col-6 col-lg-3">
                <x-stat-card icon="ti-flag" label="First Location" value="{{ $summary->first_location ?? '—' }}" color="success" />
            </div>
            <div class="col-6 col-lg-3">
                <x-stat-card icon="ti-flag-filled" label="Last Location" value="{{ $summary->last_location ?? '—' }}" color="danger" />
            </div>
        </div>

        <div class="card mb-4 hover-lift">
            <div class="card-header">
                Today's Route Overview
                @if ($summary->first_ping_at)
                    &mdash; {{ $summary->first_ping_at->format('h:i A') }} to {{ $summary->last_ping_at->format('h:i A') }}
                @endif
            </div>
            <div class="card-body">
                @if ($summary->route->isNotEmpty())
                    <div id="movementRouteMap" style="height:400px;border-radius:.5rem"></div>
                @else
                    <p class="text-muted mb-0">No GPS pings recorded for this executive on this date.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">Totals</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <tbody>
                        <tr><th>Total Locations Captured</th><td>{{ $summary->locations_captured }}</td></tr>
                        <tr><th>Total Check-ins (Visits)</th><td>{{ $summary->visits_count }}</td></tr>
                        <tr><th>Total Distance</th><td>{{ $summary->distance_km }} km</td></tr>
                        <tr><th>Total Working Duration</th><td>{{ $formatDuration($summary->working_seconds) }}</td></tr>
                        <tr><th>Total Idle Duration</th><td>{{ $formatDuration($summary->idle_seconds) }}</td></tr>
                        <tr><th>Total Visit Duration</th><td>{{ $formatDuration($summary->visit_seconds) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@if ($summary && $summary->route->isNotEmpty())
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const points = @json($summary->route->map(fn ($p) => [$p['lat'], $p['lng']]));
                const map = window.L.map('movementRouteMap');

                if (points.length > 1) {
                    window.L.polyline(points, { color: '#0d5aa7', weight: 4 }).addTo(map);
                }

                window.L.marker(points[0], { title: 'Start' }).addTo(map).bindPopup('Start');
                if (points.length > 1) {
                    window.L.marker(points[points.length - 1], { title: 'End' }).addTo(map).bindPopup('End');
                }

                map.fitBounds(points, { padding: [30, 30] });

                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(map);
            });
        </script>
    @endpush
@endif
