@extends('layouts.portal-account')

@section('title', 'My Dashboard')

@section('content')
    <h1 class="mb-1">Welcome, {{ $account->name }}</h1>
    <p class="text-muted mb-4">Here's a summary of your account and purchase history.</p>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-shopping-cart" label="Total Purchase" value="{{ $totalPurchase }}" color="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-cash" label="Total Payment" value="{{ $totalPayment }}" color="success" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-alert-circle" label="Due Amount" value="{{ $dueAmount }}" color="danger" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-receipt" label="Total Orders" value="{{ $totalOrders }}" color="warning" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Purchases vs Payment &mdash; Last 6 Months</h6>
                    <a href="{{ route('portal.purchases.index') }}" class="small">View All</a>
                </div>
                <div class="card-body">
                    @if ($totalOrders > 0)
                        <div id="customerPurchaseVsPaymentChart" data-chart-purchases-vs-payments="{{ json_encode($monthlyPurchasesVsPayments) }}"></div>
                    @else
                        <p class="text-muted small mb-0">No purchase history yet.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Purchases by Product</h6>
                </div>
                <div class="card-body">
                    @if (count($topProducts) > 0)
                        <div id="customerProductBreakdownChart" data-chart-products="{{ json_encode($topProducts) }}"></div>
                    @else
                        <p class="text-muted small mb-0">No product purchases yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Recent Inquiries</h6>
                    <a href="{{ route('portal.inquiries.index') }}" class="small">View All</a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($recentInquiries as $inquiry)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $inquiry->subject }}</span>
                            <span class="badge text-bg-{{ $inquiry->status->badgeColor() }}">{{ $inquiry->status->label() }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted small">No inquiries yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Recent Visit Requests</h6>
                    <a href="{{ route('portal.visit-requests.index') }}" class="small">View All</a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($recentVisitRequests as $visitRequest)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $visitRequest->preferred_date->format('M d, Y') }}</span>
                            <span class="badge text-bg-{{ $visitRequest->status->badgeColor() }}">{{ $visitRequest->status->label() }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted small">No visit requests yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
