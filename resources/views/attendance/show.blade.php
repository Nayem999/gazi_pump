@extends('layouts.admin')

@section('title', 'Attendance Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
    <li class="breadcrumb-item active">{{ $attendance->user->name }} &mdash; {{ $attendance->date->format('d M Y') }}</li>
@endsection

@php
    $hasCheckInGps = $attendance->check_in_lat !== null && $attendance->check_in_lng !== null;
    $hasCheckOutGps = $attendance->check_out_lat !== null && $attendance->check_out_lng !== null;
@endphp

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ti ti-id-badge-2 display-1 text-secondary mb-2 d-block"></i>
                    <h5 class="mb-0">{{ $attendance->user->name }}</h5>
                    <div class="text-muted">{{ $attendance->user->employee_id }}</div>
                    <div class="mt-2">
                        <span class="badge text-bg-{{ $attendance->status->badgeColor() }}">{{ $attendance->status->label() }}</span>
                    </div>
                    @can('update', $attendance)
                        <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-outline-primary btn-sm mt-3">
                            <i class="ti ti-pencil me-1"></i>Edit
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Attendance Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Employee Phone</dt>
                        <dd class="col-sm-8"><x-phone-actions :phone="$attendance->user->phone" /></dd>

                        <dt class="col-sm-4">Date</dt>
                        <dd class="col-sm-8">{{ $attendance->date->format('d M Y') }}</dd>

                        <dt class="col-sm-4">Check In</dt>
                        <dd class="col-sm-8">{{ $attendance->check_in_at?->format('d M Y, h:i A') ?? '—' }}</dd>

                        <dt class="col-sm-4">Check Out</dt>
                        <dd class="col-sm-8">{{ $attendance->check_out_at?->format('d M Y, h:i A') ?? '—' }}</dd>

                        <dt class="col-sm-4">Late By</dt>
                        <dd class="col-sm-8">{{ $attendance->late_minutes ? "{$attendance->late_minutes} minute(s)" : '—' }}</dd>

                        <dt class="col-sm-4">Remarks</dt>
                        <dd class="col-sm-8">{{ $attendance->remarks ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Check-In Photo</div>
                <div class="card-body text-center">
                    @if ($attendance->check_in_photo)
                        <img src="{{ $attendance->checkInPhotoUrl() }}" class="img-fluid rounded" style="max-height:280px">
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
                    @if ($attendance->check_out_photo)
                        <img src="{{ $attendance->checkOutPhotoUrl() }}" class="img-fluid rounded" style="max-height:280px">
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
                    @if ($hasCheckInGps || $hasCheckOutGps)
                        <div id="attendanceMap" style="height:320px;border-radius:.5rem"></div>
                    @else
                        <p class="text-muted mb-0">No GPS location recorded for this attendance record.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($hasCheckInGps || $hasCheckOutGps)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const points = [];
                const map = window.L.map('attendanceMap');

                @if ($hasCheckInGps)
                    const checkIn = [{{ $attendance->check_in_lat }}, {{ $attendance->check_in_lng }}];
                    points.push(checkIn);
                    window.L.marker(checkIn, { title: 'Check In' }).addTo(map).bindPopup('Check In');
                @endif

                @if ($hasCheckOutGps)
                    const checkOut = [{{ $attendance->check_out_lat }}, {{ $attendance->check_out_lng }}];
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
