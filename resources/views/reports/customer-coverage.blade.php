@extends('layouts.admin')

@section('title', 'Customer Coverage Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Customer Coverage</li>
@endsection

@section('content')
    <x-filter-bar :action="route('reports.customer-coverage')">
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
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Customer Coverage</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('reports.customer-coverage.export', request()->query()) }}" class="btn btn-outline-secondary"><i class="ti ti-file-spreadsheet me-1"></i>Export</a>
                <a href="{{ route('reports.customer-coverage.print', request()->query()) }}" class="btn btn-outline-secondary" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Territory</th>
                        <th>Total Customers</th>
                        <th>Visited</th>
                        <th>Not Visited</th>
                        <th>Coverage Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row->territory?->name }}</td>
                            <td>{{ $row->total_customers }}</td>
                            <td><span class="badge text-bg-success">{{ $row->visited_customers }}</span></td>
                            <td><span class="badge text-bg-danger">{{ $row->not_visited_customers }}</span></td>
                            <td class="fw-semibold">{{ $row->coverage_rate }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No customers found for this period.</td>
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
