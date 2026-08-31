@extends('layouts.admin')

@section('title', 'Reports')

@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
    <div class="row g-3">
        @can('report.attendance')
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('reports.attendance-summary') }}" class="card h-100 hover-lift text-decoration-none">
                    <div class="card-body">
                        <div class="stat-card-icon bg-success-subtle text-success mb-3" style="width:48px;height:48px;font-size:1.25rem">
                            <i class="ti ti-calendar-check"></i>
                        </div>
                        <h6 class="mb-1 text-body">Attendance Summary</h6>
                        <p class="text-muted small mb-0">Present/late/absent counts and attendance rate per executive.</p>
                    </div>
                </a>
            </div>
        @endcan

        @can('report.visits')
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('reports.visit-compliance') }}" class="card h-100 hover-lift text-decoration-none">
                    <div class="card-body">
                        <div class="stat-card-icon bg-info-subtle text-info mb-3" style="width:48px;height:48px;font-size:1.25rem">
                            <i class="ti ti-walk"></i>
                        </div>
                        <h6 class="mb-1 text-body">Visit Compliance</h6>
                        <p class="text-muted small mb-0">Planned vs completed visits and GPS verification rate.</p>
                    </div>
                </a>
            </div>
        @endcan

        @can('report.achievement-summary')
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('reports.achievement-summary') }}" class="card h-100 hover-lift text-decoration-none">
                    <div class="card-body">
                        <div class="stat-card-icon bg-primary-subtle text-primary mb-3" style="width:48px;height:48px;font-size:1.25rem">
                            <i class="ti ti-trophy"></i>
                        </div>
                        <h6 class="mb-1 text-body">Achievement Summary</h6>
                        <p class="text-muted small mb-0">Daily achievement entries and totals per executive.</p>
                    </div>
                </a>
            </div>
        @endcan

        @can('report.territories')
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('reports.territory-performance') }}" class="card h-100 hover-lift text-decoration-none">
                    <div class="card-body">
                        <div class="stat-card-icon bg-secondary-subtle text-secondary mb-3" style="width:48px;height:48px;font-size:1.25rem">
                            <i class="ti ti-map-pin-2"></i>
                        </div>
                        <h6 class="mb-1 text-body">Territory Performance</h6>
                        <p class="text-muted small mb-0">Orders, collections, and visit activity grouped by territory.</p>
                    </div>
                </a>
            </div>
        @endcan

        @can('report.target-achievement')
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('reports.target-achievement') }}" class="card h-100 hover-lift text-decoration-none">
                    <div class="card-body">
                        <div class="stat-card-icon bg-success-subtle text-success mb-3" style="width:48px;height:48px;font-size:1.25rem">
                            <i class="ti ti-target-arrow"></i>
                        </div>
                        <h6 class="mb-1 text-body">Target vs Achievement</h6>
                        <p class="text-muted small mb-0">Monthly order/collection targets against actual achievement and grade.</p>
                    </div>
                </a>
            </div>
        @endcan

        @can('report.executive-performance')
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('reports.executive-performance') }}" class="card h-100 hover-lift text-decoration-none">
                    <div class="card-body">
                        <div class="stat-card-icon bg-primary-subtle text-primary mb-3" style="width:48px;height:48px;font-size:1.25rem">
                            <i class="ti ti-user-star"></i>
                        </div>
                        <h6 class="mb-1 text-body">Executive Performance</h6>
                        <p class="text-muted small mb-0">A combined monthly scorecard: attendance, visits, orders, collections, achievement.</p>
                    </div>
                </a>
            </div>
        @endcan

        @can('report.dealer-coverage')
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('reports.dealer-coverage') }}" class="card h-100 hover-lift text-decoration-none">
                    <div class="card-body">
                        <div class="stat-card-icon bg-info-subtle text-info mb-3" style="width:48px;height:48px;font-size:1.25rem">
                            <i class="ti ti-building-store"></i>
                        </div>
                        <h6 class="mb-1 text-body">Dealer Coverage</h6>
                        <p class="text-muted small mb-0">Visited vs not-visited dealers per territory for the period.</p>
                    </div>
                </a>
            </div>
        @endcan

        @can('report.gps')
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('reports.gps-report') }}" class="card h-100 hover-lift text-decoration-none">
                    <div class="card-body">
                        <div class="stat-card-icon bg-warning-subtle text-warning mb-3" style="width:48px;height:48px;font-size:1.25rem">
                            <i class="ti ti-map-pin"></i>
                        </div>
                        <h6 class="mb-1 text-body">GPS Report</h6>
                        <p class="text-muted small mb-0">Ping volume, accuracy, and last-seen per executive.</p>
                    </div>
                </a>
            </div>
        @endcan

        @can('report.movement-summary')
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('reports.movement-summary') }}" class="card h-100 hover-lift text-decoration-none">
                    <div class="card-body">
                        <div class="stat-card-icon bg-success-subtle text-success mb-3" style="width:48px;height:48px;font-size:1.25rem">
                            <i class="ti ti-route-2"></i>
                        </div>
                        <h6 class="mb-1 text-body">Movement Summary</h6>
                        <p class="text-muted small mb-0">One executive's day: working hours, route, distance, idle time, and visits.</p>
                    </div>
                </a>
            </div>
        @endcan
    </div>

    @canany(['report.attendance', 'report.visits', 'report.achievement-summary', 'report.territories', 'report.target-achievement', 'report.executive-performance', 'report.dealer-coverage', 'report.gps', 'report.movement-summary'])
    @else
        <div class="text-center text-muted py-5">
            <i class="ti ti-lock display-4 d-block mb-2"></i>
            You don't have access to any reports.
        </div>
    @endcanany
@endsection
