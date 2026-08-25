@extends('layouts.admin')

@section('title', 'Dealer & Ledger Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Dealer &amp; Ledger Report</li>
@endsection

@section('content')
    <x-filter-bar :action="route('reports.dealer-ledger')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Dealer name or code" value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Division</label>
            <select name="division_id" id="filterDivision" class="form-select">
                <option value="">All</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" @selected((string) ($filters['division_id'] ?? '') === (string) $division->id)>{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">District</label>
            <select name="district_id" id="filterDistrict" class="form-select" @disabled(empty($filters['division_id']))>
                <option value="">All</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Thana</label>
            <select name="thana_id" id="filterThana" class="form-select" @disabled(empty($filters['district_id']))>
                <option value="">All</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Territory</label>
            <select name="territory_id" id="filterTerritory" class="form-select" @disabled(empty($filters['thana_id']))>
                <option value="">All</option>
                @foreach ($territories as $territory)
                    <option value="{{ $territory->id }}" @selected((string) ($filters['territory_id'] ?? '') === (string) $territory->id)>{{ $territory->name }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Dealer &amp; Ledger Report</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('reports.dealer-ledger.export', request()->query()) }}" class="btn btn-outline-secondary"><i class="ti ti-file-spreadsheet me-1"></i>Export</a>
                <a href="{{ route('reports.dealer-ledger.print', request()->query()) }}" class="btn btn-outline-secondary" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Dealer</th>
                        <th>Territory</th>
                        <th>Total Ordered</th>
                        <th>Total Collected</th>
                        <th>Due Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>
                                {{ $row->dealer->name }}
                                <div class="text-muted small">{{ $row->dealer->dealer_code }}</div>
                            </td>
                            <td>{{ $row->dealer->territory?->name ?? '—' }}</td>
                            <td>{{ number_format($row->total_ordered, 2) }}</td>
                            <td>{{ number_format($row->total_collected, 2) }}</td>
                            <td class="fw-semibold {{ $row->due_amount > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($row->due_amount, 2) }}</td>
                            <td>
                                <a href="{{ route('reports.dealer-ledger.show', $row->dealer) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-file-text me-1"></i>Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No dealers found.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($rows->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-end fw-semibold">Grand Total</td>
                            <td class="fw-semibold">{{ number_format($totals['total_ordered'], 2) }}</td>
                            <td class="fw-semibold">{{ number_format($totals['total_collected'], 2) }}</td>
                            <td class="fw-semibold">{{ number_format($totals['due_amount'], 2) }}</td>
                            <td></td>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterDivision = document.getElementById('filterDivision');
            const filterDistrict = document.getElementById('filterDistrict');
            const filterThana = document.getElementById('filterThana');
            const filterTerritory = document.getElementById('filterTerritory');

            initCascadingSelect(filterDivision, filterDistrict, '{{ route('districts.options') }}', 'division_id', {
                initialChildValue: '{{ $filters['district_id'] ?? '' }}',
            });
            initCascadingSelect(filterDistrict, filterThana, '{{ route('thanas.options') }}', 'district_id', {
                initialChildValue: '{{ $filters['thana_id'] ?? '' }}',
            });
        });
    </script>
@endpush
