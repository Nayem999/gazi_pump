@extends('layouts.admin')

@section('title', 'Visit Compliance Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Visit Compliance</li>
@endsection

@section('content')
    <x-filter-bar :action="route('reports.visit-compliance')">
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
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? now()->startOfMonth()->toDateString() }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? now()->endOfMonth()->toDateString() }}">
        </div>
    </x-filter-bar>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Visit Compliance</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('reports.visit-compliance.export', request()->query()) }}" class="btn btn-outline-secondary"><i class="ti ti-file-spreadsheet me-1"></i>Export</a>
                <a href="{{ route('reports.visit-compliance.print', request()->query()) }}" class="btn btn-outline-secondary" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Executive</th>
                        <th>Territory</th>
                        <th>Planned</th>
                        <th>Completed</th>
                        <th>Missed</th>
                        <th>Completion Rate</th>
                        <th>Total Visits</th>
                        <th>GPS Verified</th>
                        <th>GPS Verified Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>
                                {{ $row->user?->name }}
                                <div class="text-muted small">{{ $row->user?->employee_id }}</div>
                            </td>
                            <td>{{ $row->user?->territory?->name ?? '—' }}</td>
                            <td>{{ $row->planned_count }}</td>
                            <td><span class="badge text-bg-success">{{ $row->completed_count }}</span></td>
                            <td><span class="badge text-bg-danger">{{ $row->missed_count }}</span></td>
                            <td class="fw-semibold">{{ $row->completion_rate }}%</td>
                            <td>{{ $row->total_visits }}</td>
                            <td>{{ $row->gps_verified_count }}</td>
                            <td class="fw-semibold">{{ $row->gps_verified_rate }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No visit data for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($rows->hasPages())
            <div class="card-footer bg-white">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
@endsection
