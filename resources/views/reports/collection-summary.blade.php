@extends('layouts.admin')

@section('title', 'Collection Summary Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Collection Summary</li>
@endsection

@section('content')
    <x-filter-bar :action="route('reports.collection-summary')">
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
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Collection Summary</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('reports.collection-summary.export', request()->query()) }}" class="btn btn-outline-secondary"><i class="ti ti-file-spreadsheet me-1"></i>Export</a>
                <a href="{{ route('reports.collection-summary.print', request()->query()) }}" class="btn btn-outline-secondary" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Executive</th>
                        <th>Territory</th>
                        <th>Collections</th>
                        <th>Total Amount</th>
                        <th>Cash</th>
                        <th>Cheque</th>
                        <th>Bank Transfer</th>
                        <th>Mobile Banking</th>
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
                            <td>{{ $row->collections_count }}</td>
                            <td class="fw-semibold">{{ number_format($row->total_amount, 2) }}</td>
                            <td>{{ number_format($row->cash_total, 2) }}</td>
                            <td>{{ number_format($row->cheque_total, 2) }}</td>
                            <td>{{ number_format($row->bank_transfer_total, 2) }}</td>
                            <td>{{ number_format($row->mobile_banking_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No collection data for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($rows->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-end fw-semibold">Grand Total</td>
                            <td class="fw-semibold">{{ $totals['collections_count'] }}</td>
                            <td class="fw-semibold">{{ number_format($totals['total_amount'], 2) }}</td>
                            <td class="fw-semibold">{{ number_format($totals['cash_total'], 2) }}</td>
                            <td class="fw-semibold">{{ number_format($totals['cheque_total'], 2) }}</td>
                            <td class="fw-semibold">{{ number_format($totals['bank_transfer_total'], 2) }}</td>
                            <td class="fw-semibold">{{ number_format($totals['mobile_banking_total'], 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        @if ($rows->hasPages())
            <div class="card-footer">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
@endsection
