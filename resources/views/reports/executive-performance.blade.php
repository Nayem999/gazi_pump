@extends('layouts.admin')

@section('title', 'Executive Performance Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Executive Performance</li>
@endsection

@section('content')
    <x-filter-bar :action="route('reports.executive-performance')">
        <div class="col-md-3">
            <label class="form-label">Executive</label>
            <select name="user_id" class="form-select">
                <option value="">All</option>
                @foreach ($executives as $executive)
                    <option value="{{ $executive->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $executive->id)>{{ $executive->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Territory</label>
            <select name="territory_id" class="form-select">
                <option value="">All</option>
                @foreach ($territories as $territory)
                    <option value="{{ $territory->id }}" @selected((string) ($filters['territory_id'] ?? '') === (string) $territory->id)>{{ $territory->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Month</label>
            <select name="month" class="form-select">
                @foreach (range(1, 12) as $month)
                    <option value="{{ $month }}" @selected($filters['month'] === $month)>{{ \Illuminate\Support\Carbon::create(2000, $month, 1)->format('F') }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control" value="{{ $filters['year'] }}">
        </div>
    </x-filter-bar>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Executive Performance</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('reports.executive-performance.export', request()->query()) }}" class="btn btn-outline-secondary"><i class="ti ti-file-spreadsheet me-1"></i>Export</a>
                <a href="{{ route('reports.executive-performance.print', request()->query()) }}" class="btn btn-outline-secondary" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Executive</th>
                        <th>Attendance</th>
                        <th>Visit Completion</th>
                        <th>GPS Verified</th>
                        <th>Order Value</th>
                        <th>Collections</th>
                        <th>Overall Achievement</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>
                                {{ $row->user?->name }}
                                <div class="text-muted small">{{ $row->user?->territory?->name ?? '—' }}</div>
                            </td>
                            <td>{{ $row->attendance_rate }}%</td>
                            <td>{{ $row->visit_completion_rate }}%</td>
                            <td>{{ $row->gps_verified_rate }}%</td>
                            <td>{{ number_format($row->total_order_value, 2) }}</td>
                            <td>{{ number_format($row->total_collection_amount, 2) }}</td>
                            <td class="fw-semibold">{{ $row->overall_achievement_pct }}%</td>
                            <td>
                                @if ($row->grade)
                                    <span class="badge text-bg-{{ $row->grade->badgeColor() }}">{{ $row->grade->label() }}</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No executive activity for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($rows->hasPages())
            <div class="card-footer">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
@endsection
