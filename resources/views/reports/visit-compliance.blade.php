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
                        <th class="text-end">Orders</th>
                        <th class="text-end">Order Value</th>
                        <th class="text-end">Avg Order Value</th>
                        <th class="text-end"
                            title="Dealers an order came from in this period, whether or not they were visited. Rejected orders are not counted.">
                            Productive Dealers
                        </th>
                        <th class="text-end"
                            title="Of the dealers this executive visited, the share who also placed an order with them in this period.">
                            Strike Rate
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>
                                {{ $row->user?->name }}
                                <div class="text-muted small">{{ $row->user?->employee_id }}</div>
                            </td>
                            <td>{{ $row->user?->territory_names ?? '—' }}</td>
                            <td>{{ $row->planned_count }}</td>
                            <td><span class="badge text-bg-success">{{ $row->completed_count }}</span></td>
                            <td><span class="badge text-bg-danger">{{ $row->missed_count }}</span></td>
                            <td class="fw-semibold">{{ $row->completion_rate }}%</td>
                            <td>{{ $row->total_visits }}</td>
                            <td>{{ $row->gps_verified_count }}</td>
                            <td class="fw-semibold">{{ $row->gps_verified_rate }}%</td>
                            <td class="text-end">{{ $row->order_count }}</td>
                            <td class="text-end fw-semibold">{{ number_format($row->order_value, 2) }}</td>
                            <td class="text-end">{{ number_format($row->avg_order_value, 2) }}</td>
                            <td class="text-end">
                                <span class="badge text-bg-primary">{{ $row->productive_dealers }}</span>
                            </td>
                            <td class="text-end">
                                @if ($row->visited_dealers > 0)
                                    <span class="fw-semibold">{{ $row->strike_rate }}%</span>
                                    {{-- The fraction is shown so the rate cannot be
                                         misread against Productive Dealers, which
                                         also counts dealers ordering without a visit. --}}
                                    <div class="text-muted small">{{ $row->converted_dealers }} of {{ $row->visited_dealers }} visited</div>
                                @else
                                    <span class="text-muted" title="No dealers visited, so there is nothing to judge a strike rate against.">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center text-muted py-4">No visit data for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($rows->total() > 0)
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="9" class="text-end">Total, all executives</td>
                            <td class="text-end">{{ $totals['order_count'] }}</td>
                            <td class="text-end">{{ number_format($totals['order_value'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['avg_order_value'], 2) }}</td>
                            {{-- Not summed: these are distinct-dealer counts per
                                 executive, so a total would count a dealer
                                 served by two executives twice. --}}
                            <td class="text-end text-muted" title="Not totalled: a dealer served by two executives would be counted twice.">&mdash;</td>
                            <td class="text-end text-muted" title="Not totalled: a dealer served by two executives would be counted twice.">&mdash;</td>
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
