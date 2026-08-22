@extends('layouts.admin')

@section('title', $dealer->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dealers.index') }}">Dealers</a></li>
    <li class="breadcrumb-item active">{{ $dealer->name }}</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ti ti-building-store display-1 text-secondary mb-2 d-block"></i>
                    <h5 class="mb-0">{{ $dealer->name }}</h5>
                    <div class="text-muted">{{ $dealer->dealer_code }}</div>
                    <div class="mt-2">
                        <span class="badge text-bg-{{ $dealer->type->badgeColor() }}">{{ $dealer->type->label() }}</span>
                        <span class="badge text-bg-{{ $dealer->status ? 'success' : 'secondary' }}">
                            {{ $dealer->status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-2 d-print-none">
                        @can('update', $dealer)
                            <a href="{{ route('dealers.edit', $dealer) }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-pencil me-1"></i>Edit
                            </a>
                        @endcan
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="ti ti-printer me-1"></i>Print
                        </button>
                        <a href="{{ route('dealers.download-pdf', $dealer) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-file-download me-1"></i>Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Profile Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8"><x-phone-actions :phone="$dealer->phone" /></dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $dealer->email ?? '—' }}</dd>

                        <dt class="col-sm-4">Address</dt>
                        <dd class="col-sm-8">{{ $dealer->address ?? '—' }}</dd>

                        <dt class="col-sm-4">Division</dt>
                        <dd class="col-sm-8">{{ $dealer->division?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">District</dt>
                        <dd class="col-sm-8">{{ $dealer->district?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Thana / Upazila</dt>
                        <dd class="col-sm-8">{{ $dealer->thana?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Territory</dt>
                        <dd class="col-sm-8">{{ $dealer->territory?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8">{{ $dealer->created_at?->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">GPS Location</div>
                <div class="card-body">
                    @if ($dealer->hasGps())
                        <div id="dealerMap" style="height:320px;border-radius:.5rem"></div>
                    @else
                        <p class="text-muted mb-0">No GPS location recorded for this dealer yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Visit History</div>
                <div class="card-body">
                    <p class="text-muted mb-0 small">No visits recorded yet &mdash; populates once the Visit Management module is built.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Order History</div>
                <div class="card-body">
                    <p class="text-muted mb-0 small">No orders recorded yet &mdash; populates once the Order module is built.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Collection History</div>
                <div class="card-body">
                    <p class="text-muted mb-0 small">No collections recorded yet &mdash; populates once the Collection Entry module is built.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($dealer->hasGps())
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const map = window.L.map('dealerMap').setView([{{ $dealer->gps_lat }}, {{ $dealer->gps_lng }}], 15);
                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(map);
                window.L.marker([{{ $dealer->gps_lat }}, {{ $dealer->gps_lng }}])
                    .addTo(map)
                    .bindPopup(@json($dealer->name));
            });
        </script>
    @endpush
@endif
