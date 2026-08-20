@extends('layouts.admin')

@section('title', $customer->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
    <li class="breadcrumb-item active">{{ $customer->name }}</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ti ti-building-store display-1 text-secondary mb-2 d-block"></i>
                    <h5 class="mb-0">{{ $customer->name }}</h5>
                    <div class="text-muted">{{ $customer->customer_code }}</div>
                    <div class="mt-2">
                        <span class="badge text-bg-{{ $customer->type->badgeColor() }}">{{ $customer->type->label() }}</span>
                        <span class="badge text-bg-{{ $customer->status ? 'success' : 'secondary' }}">
                            {{ $customer->status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    @can('update', $customer)
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary btn-sm mt-3">
                            <i class="ti ti-pencil me-1"></i>Edit
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Profile Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">{{ $customer->phone }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $customer->email ?? '—' }}</dd>

                        <dt class="col-sm-4">Address</dt>
                        <dd class="col-sm-8">{{ $customer->address ?? '—' }}</dd>

                        <dt class="col-sm-4">Territory</dt>
                        <dd class="col-sm-8">{{ $customer->territory?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8">{{ $customer->created_at?->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">GPS Location</div>
                <div class="card-body">
                    @if ($customer->hasGps())
                        <div id="customerMap" style="height:320px;border-radius:.5rem"></div>
                    @else
                        <p class="text-muted mb-0">No GPS location recorded for this customer yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">Visit History</div>
                <div class="card-body">
                    <p class="text-muted mb-0 small">No visits recorded yet &mdash; populates once the Visit Management module is built.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">Sales History</div>
                <div class="card-body">
                    <p class="text-muted mb-0 small">No sales recorded yet &mdash; populates once the Sales Entry module is built.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">Collection History</div>
                <div class="card-body">
                    <p class="text-muted mb-0 small">No collections recorded yet &mdash; populates once the Collection Entry module is built.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($customer->hasGps())
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const map = window.L.map('customerMap').setView([{{ $customer->gps_lat }}, {{ $customer->gps_lng }}], 15);
                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(map);
                window.L.marker([{{ $customer->gps_lat }}, {{ $customer->gps_lng }}])
                    .addTo(map)
                    .bindPopup(@json($customer->name));
            });
        </script>
    @endpush
@endif
