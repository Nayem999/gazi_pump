@extends('layouts.admin')

@section('title', 'Dealer Ledger — '.$dealer->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.dealer-ledger') }}">Dealer &amp; Ledger Report</a></li>
    <li class="breadcrumb-item active">{{ $dealer->name }}</li>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="mb-1">{{ $dealer->name }}</h5>
                <div class="text-muted small">
                    {{ $dealer->dealer_code }}
                    @if ($dealer->phone) &middot; {{ $dealer->phone }} @endif
                    @if ($dealer->territory) &middot; {{ $dealer->territory->name }} @endif
                </div>
            </div>
            <div class="text-end">
                <div class="text-muted small">Current Balance</div>
                <div class="fs-4 fw-semibold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($balance, 2) }}</div>
            </div>
            <div class="btn-group btn-group-sm align-self-center">
                <a href="{{ route('reports.dealer-ledger.show-print', $dealer) }}" class="btn btn-outline-secondary" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Ledger</h6>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row->date->format('M d, Y') }}</td>
                            <td>{{ $row->description }}</td>
                            <td>{{ $row->debit > 0 ? number_format($row->debit, 2) : '—' }}</td>
                            <td>{{ $row->credit > 0 ? number_format($row->credit, 2) : '—' }}</td>
                            <td class="fw-semibold">{{ number_format($row->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No orders or collections recorded for this dealer yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
