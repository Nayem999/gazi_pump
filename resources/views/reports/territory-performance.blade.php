@extends('layouts.admin')

@section('title', 'Territory Performance Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Territory Performance</li>
@endsection

@section('content')
    <x-filter-bar :action="route('reports.territory-performance')">
        <div class="col-md-4">
            <label class="form-label">Territory</label>
            <select name="territory_id" class="form-select">
                <option value="">All</option>
                @foreach ($territories as $territory)
                    <option value="{{ $territory->id }}" @selected((string) ($filters['territory_id'] ?? '') === (string) $territory->id)>{{ $territory->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? now()->startOfMonth()->toDateString() }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? now()->endOfMonth()->toDateString() }}">
        </div>
    </x-filter-bar>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Territory Performance</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('reports.territory-performance.export', request()->query()) }}" class="btn btn-outline-secondary"><i class="ti ti-file-spreadsheet me-1"></i>Export</a>
                <a href="{{ route('reports.territory-performance.print', request()->query()) }}" class="btn btn-outline-secondary" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Territory</th>
                        <th>Executives</th>
                        <th>Total Order Value</th>
                        <th>Total Collection Amount</th>
                        <th>Total Visits</th>
                        <th>GPS Verified Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row->territory?->name }}</td>
                            <td>{{ $row->executive_count }}</td>
                            <td class="fw-semibold">{{ number_format($row->total_order_value, 2) }}</td>
                            <td>{{ number_format($row->total_collection_amount, 2) }}</td>
                            <td>{{ $row->total_visits }}</td>
                            <td>{{ $row->gps_verified_rate }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No territory data for this period.</td>
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
