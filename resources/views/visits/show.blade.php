@extends('layouts.admin')

@section('title', 'Visit Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('visits.index') }}">Dealer Visits</a></li>
    <li class="breadcrumb-item active">{{ $visit->dealer->name }} &mdash; {{ $visit->check_in_at->format('d M Y') }}</li>
@endsection

@php
    $hasCheckInGps = $visit->check_in_lat !== null && $visit->check_in_lng !== null;
    $hasCheckOutGps = $visit->check_out_lat !== null && $visit->check_out_lng !== null;
    $hasDealerGps = $visit->dealer->gps_lat !== null && $visit->dealer->gps_lng !== null;
@endphp

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ti ti-building-store display-1 text-secondary mb-2 d-block"></i>
                    <h5 class="mb-0">
                        @if (! $visit->dealer->trashed())
                            <a href="{{ route('dealers.show', $visit->dealer) }}">{{ $visit->dealer->name }}</a>
                        @else
                            {{ $visit->dealer->name }}
                        @endif
                    </h5>
                    <div class="text-muted">{{ $visit->dealer->dealer_code }}</div>
                    <div class="mt-2">
                        <span class="badge text-bg-{{ match ($visit->is_gps_verified) { true => 'success', false => 'danger', default => 'secondary' } }}">
                            {{ match ($visit->is_gps_verified) { true => 'GPS Verified', false => 'GPS Unverified', default => 'GPS Unknown' } }}
                        </span>
                    </div>
                    @can('update', $visit)
                        <a href="{{ route('visits.edit', $visit) }}" class="btn btn-outline-primary btn-sm mt-3">
                            <i class="ti ti-pencil me-1"></i>Edit
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Visit Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Sales Executive</dt>
                        <dd class="col-sm-8">{{ $visit->user->name }} ({{ $visit->user->employee_id }})</dd>

                        <dt class="col-sm-4">Executive Phone</dt>
                        <dd class="col-sm-8"><x-phone-actions :phone="$visit->user->phone" /></dd>

                        <dt class="col-sm-4">Dealer Phone</dt>
                        <dd class="col-sm-8"><x-phone-actions :phone="$visit->dealer->phone" /></dd>

                        <dt class="col-sm-4">Visit Plan</dt>
                        <dd class="col-sm-8">
                            @if ($visit->visitPlan)
                                <a href="{{ route('visit-plans.edit', $visit->visitPlan) }}">{{ $visit->visitPlan->planned_date->format('d M Y') }}</a>
                            @else
                                — (unplanned visit)
                            @endif
                        </dd>

                        <dt class="col-sm-4">Check In</dt>
                        <dd class="col-sm-8">{{ $visit->check_in_at->format('d M Y, h:i A') }}</dd>

                        <dt class="col-sm-4">Check Out</dt>
                        <dd class="col-sm-8">{{ $visit->check_out_at?->format('d M Y, h:i A') ?? '—' }}</dd>

                        <dt class="col-sm-4">Distance from Dealer</dt>
                        <dd class="col-sm-8">{{ $visit->distance_from_dealer_meters !== null ? number_format((float) $visit->distance_from_dealer_meters, 1).' m' : '—' }}</dd>

                        <dt class="col-sm-4">Feedback</dt>
                        <dd class="col-sm-8">{{ $visit->feedback ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Check-In Photo</div>
                <div class="card-body text-center">
                    @if ($visit->check_in_photo)
                        <img src="{{ $visit->checkInPhotoUrl() }}" class="img-fluid rounded" style="max-height:280px">
                    @else
                        <p class="text-muted mb-0">No check-in photo captured.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Check-Out Photo</div>
                <div class="card-body text-center">
                    @if ($visit->check_out_photo)
                        <img src="{{ $visit->checkOutPhotoUrl() }}" class="img-fluid rounded" style="max-height:280px">
                    @else
                        <p class="text-muted mb-0">No check-out photo captured.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">GPS Locations</div>
                <div class="card-body">
                    @if ($hasCheckInGps || $hasCheckOutGps || $hasDealerGps)
                        <div id="visitMap" style="height:320px;border-radius:.5rem"></div>
                    @else
                        <p class="text-muted mb-0">No GPS location recorded for this visit.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($hasCheckInGps || $hasCheckOutGps || $hasDealerGps)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const points = [];
                const map = window.L.map('visitMap');

                @if ($hasDealerGps)
                    const dealerLoc = [{{ $visit->dealer->gps_lat }}, {{ $visit->dealer->gps_lng }}];
                    points.push(dealerLoc);
                    window.L.marker(dealerLoc, { title: 'Dealer' }).addTo(map).bindPopup('Registered Dealer Location');
                @endif

                @if ($hasCheckInGps)
                    const checkIn = [{{ $visit->check_in_lat }}, {{ $visit->check_in_lng }}];
                    points.push(checkIn);
                    window.L.marker(checkIn, { title: 'Check In' }).addTo(map).bindPopup('Check In');
                @endif

                @if ($hasCheckOutGps)
                    const checkOut = [{{ $visit->check_out_lat }}, {{ $visit->check_out_lng }}];
                    points.push(checkOut);
                    window.L.marker(checkOut, { title: 'Check Out' }).addTo(map).bindPopup('Check Out');
                @endif

                if (points.length > 1) {
                    map.fitBounds(points, { padding: [30, 30] });
                } else {
                    map.setView(points[0], 15);
                }

                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(map);
            });
        </script>
    @endpush
@endif
